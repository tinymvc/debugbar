<?php

namespace DebugBar;

use DebugBar\Contracts\DebugBarContract;
use Spark\Contracts\Support\Jsonable;
use Spark\Facades\Blade;

/**
 * Class DebugBar
 *
 * A lightweight debugging toolbar for TinyMVC Framework.
 * Collects and displays performance metrics, database queries,
 * view rendering times, memory usage, and more.
 *
 * @package DebugBar
 * @version 1.0.0
 * @author Shahin Moyshan <shahin.moyshan2@gmail.com>
 * @license MIT
 * @link https://github.com/tinymvc/debugbar
 * @see https://github.com/tinymvc/tinymvc
 */
class DebugBar implements DebugBarContract
{
    /** @var array $logs The log messages collected by the DebugBar */
    protected array $logs = [];

    /** @var array $queries The database queries executed during the request */
    protected array $queries = [];

    /** @var array $viewsRendered The views/templates rendered during the request */
    protected array $viewsRendered = [];

    /** @var array $languageFiles The language files loaded during the request */
    protected array $languageFiles = [];

    /** @var array $routeInfo Information about the matched route */
    protected array $routeInfo = [];

    /** @var array $middlewares The middleware stack processed for the request */
    protected array $middlewares = [];

    /** @var array $timers Named timers for measuring code execution segments */
    protected array $timers = [];

    /** @var array $memorySnapshots Memory usage snapshots taken at various points */
    protected array $memorySnapshots = [];

    /** @var float $appStartTime The timestamp when the application started */
    protected float $appStartTime;

    /** @var float $appEndTime The timestamp when the application ended */
    protected float $appEndTime;

    /** @var int $appStartMemory Memory usage at application start */
    protected int $appStartMemory;

    /** @var int $appEndMemory Memory usage at application end */
    protected int $appEndMemory;

    /** @var int $appPeakMemory Peak memory usage during the application lifecycle */
    protected int $appPeakMemory;

    /** @var int $peakMemoryUsage The peak memory usage during the application lifecycle */
    protected int $peakMemoryUsage;

    /** @var int $currentMemoryUsage The current memory usage at the latest measurement */
    protected int $currentMemoryUsage;

    /** @var bool $isCollectingData Flag to enable/disable data collection */
    protected bool $isCollectingData = true;

    /** @var self $instance Static instance for global access */
    public static self $instance;

    /**
     * This constructor initializes the DebugBar, sets up event listeners,
     * and prepares it to collect data throughout the application lifecycle.
     * 
     * It also disables itself automatically when running in CLI mode.
     * 
     * @param array $options Optional configuration options
     * 
     * @return void
     */
    public function __construct(private array $options = [])
    {
        // Store global reference for static access
        self::$instance = $this;

        $this->options = array_merge([
            'record' => false,
            'max_records' => 100,
            'show_debugbar' => true,
            'show_ajax' => true,
        ], $options);

        if (php_sapi_name() === 'cli') {
            // Disable DebugBar in CLI mode
            $this->isCollectingData = false;
            return;
        }

        // Handle requests to the DebugBar interface
        if (
            $_SERVER['REQUEST_METHOD'] === 'GET' &&
            strpos($_SERVER['REQUEST_URI'], '/debugbar') !== false
        ) {
            $this->handleDebugBarRequest();
            exit;
        }

        $this->appStartTime = defined('APP_STARTED') ? APP_STARTED : microtime(true);
        $this->appStartMemory = (int) memory_get_usage(true);

        // Initialize memory properties to current state
        $this->updateMemoryState();

        // Take initial memory snapshot
        $this->memorySnapshots['app_start'] = [
            'usage' => $this->appStartMemory,
            'peak' => $this->peakMemoryUsage,
            'timestamp' => $this->appStartTime,
            'label' => 'Application Start'
        ];

        $this->registerEventListeners();
    }

    /**
     * Handles requests to the DebugBar interface for viewing snapshots.
     * Renders either a list of snapshots or a detailed view of a specific snapshot.
     * 
     * @return void
     */
    private function handleDebugBarRequest()
    {
        Blade::setPath(__DIR__ . '/resources/views');

        $path = $_SERVER['REQUEST_URI'];
        $file = basename($path);
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $filepath = storage_dir("/temp/debugbar/$file");
            if (!file_exists($filepath)) {
                http_response_code(404);
                echo 'Snapshot not found';
                exit;
            }

            $context = json_decode(file_get_contents($filepath), true);
            foreach ($context['data'] as $key => $value) {
                Blade::share($key, $value);
            }

            Blade::share('isFull', true);
            echo view('debugbar')
                ->send();

            return; // Exit after rendering the full debugbar view
        }

        $snapshots = [];
        foreach (glob(storage_dir('/temp/debugbar/snapshot_*.json')) as $file) {
            $snapshot = json_decode(file_get_contents($file), true);
            $snapshots[] = [
                'file' => basename($file),
                'time' => $snapshot['time'],
                'method' => $snapshot['data']['request']['method'] ?? 'GET',
                'url' => $snapshot['data']['request']['url'] ?? '/',
                'summary' => [
                    'execution_time' => $snapshot['data']['performance']['execution_time'] ?? 'N/A',
                    'memory_used' => $snapshot['data']['performance']['memory_used'] ?? 'N/A',
                    'database_query_count' => $snapshot['data']['database']['query_count'] ?? 0,
                ],
            ];
        }

        $snapshots = array_reverse($snapshots);

        view('debuglist', compact('snapshots'))
            ->send(); // Render the list of snapshots
    }

    /**
     * Static method to register and get the DebugBar instance
     * 
     * @param array $options Optional configuration options
     *              - record: bool Whether to record the entire request snapshots
     *              - max_records: int Maximum number of request snapshots to keep
     *              - show_debugbar: bool Whether to enable the bottom debug bar
     *              - show_ajax: bool Whether to enable AJAX support for the debug bar
     * 
     * @return self The DebugBar instance
     */
    public static function register(array $options = []): self
    {
        return new self($options);
    }

    /**
     * Static method to manually capture current memory usage
     * and log it with an optional label.
     * 
     * @param string $label Optional label for the memory snapshot
     * @return void
     */
    public static function captureMemory(string $label = 'Manual Capture'): void
    {
        self::$instance->updateMemoryState();
        self::$instance->takeMemorySnapshot('manual_' . microtime(true), $label);
        self::$instance->log(
            "Manual memory capture: {$label}",
            [
                'current_memory' => self::$instance->formatBytes(self::$instance->currentMemoryUsage),
                'peak_memory' => self::$instance->formatBytes(self::$instance->peakMemoryUsage)
            ],
            'info',
            'Manual'
        );
    }

    /**
     * Registers event listeners to hook into application lifecycle,
     * routing, database queries, view rendering, and translation loading.
     * Each listener captures relevant data and updates the DebugBar state.
     * 
     * @return void
     */
    protected function registerEventListeners(): void
    {
        // Application lifecycle events
        event([
            'app:booting' => function () {
                if (!$this->isCollectingData)
                    return;

                $this->updateMemoryState();
                $this->startTimer('app_boot');
                $this->log('Application booting', [], 'info', 'Application');
            }
        ]);

        event([
            'app:booted' => function () {
                if (!$this->isCollectingData)
                    return;

                $this->stopTimer('app_boot');
                $this->startTimer('route_execution');
                $this->startTimer('route_matching');
                $this->updateMemoryState();
                $this->takeMemorySnapshot('app_booted', 'Application Booted');
                $this->log('Application booted', [], 'info', 'Application');

                ob_start(); // Start output buffering
            }
        ]);

        event([
            'app:terminated' => function () {
                if (!$this->isCollectingData)
                    return;

                $this->appEndTime = microtime(true);
                $this->appEndMemory = (int) memory_get_usage(true);
                $this->appPeakMemory = (int) memory_get_peak_usage(true);

                $output = ob_get_clean();

                $this->log('Application terminated', [], 'info', 'Application');

                // Final memory snapshot before generating debug data
                $this->takeMemorySnapshot('app_terminated', 'Application Terminated');

                // Record the request recording is enabled
                if ($this->options['record']) {
                    $this->recordThisRequest();
                }

                // Check if output is JSON (API response)
                $decoded = json_decode($output, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    if ($this->options['show_ajax']) {
                        $decoded['__debug_bar'] = $this->serializedDebugData($this->getJsonSnapshot());
                        $output = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    }
                } else {
                    if ($this->options['show_debugbar']) {
                        // Set the path for DebugBar blade templates
                        foreach ($this->serializedDebugData($this->getDebugData()) as $key => $value) {
                            Blade::share($key, $value);
                        }

                        Blade::share('isFull', false);
                        Blade::setPath(__DIR__ . '/resources/views');

                        // Render the debug bar with all collected data
                        $output .= Blade::render('debugbar');
                    }
                }

                echo $output; // Output the final content
            }
        ]);

        // Route events with timing
        event([
            'app:routeMatched' => function ($route = []) {
                if (!$this->isCollectingData)
                    return;

                $this->routeInfo = $route; // Capture route info
    
                $this->stopTimer('route_matching');
                $this->startTimer('middleware_processing');
                $this->updateMemoryState();
                $this->takeMemorySnapshot('route_matched', 'Route Matched');
                $this->log('Route matched', $route, 'info', 'Routing');
            }
        ]);

        event([
            'app:middlewaresHandled' => function ($middlewareData = []) {
                if (!$this->isCollectingData)
                    return;

                // Capture middleware stack
                $this->middlewares = array_merge($this->middlewares, $middlewareData);

                $this->stopTimer('middleware_processing');
                $this->startTimer('route_callback_processing');
                $this->updateMemoryState();
                $this->takeMemorySnapshot('middlewares_processed', 'Middlewares Processed');
                $this->log('Middlewares processed', $middlewareData, 'info', 'Routing');
            }
        ]);

        event([
            'app:routeDispatched' => function () {
                if (!$this->isCollectingData)
                    return;

                $this->stopTimer('route_callback_processing');
                $this->stopTimer('route_execution');
                $this->updateMemoryState();
                $this->takeMemorySnapshot('route_completed', 'Route Execution Complete');
                $this->log('Route execution completed', [], 'info', 'Routing');
            }
        ]);

        // Database events with precise timing
        event([
            'app:db.queryExecuted' => function ($data) {
                if (!$this->isCollectingData)
                    return;

                $queryTimestamp = microtime(true);

                // Get memory BEFORE processing the query data (closest to actual query execution)
                $memoryBefore = $data['memory_before'] ?? (int) memory_get_usage(true);
                $memoryAfter = (int) memory_get_usage(true);

                // Calculate actual memory delta (could be negative if memory was freed)
                $memoryDelta = $memoryAfter - $memoryBefore;

                $this->queries[] = [
                    'query' => $data['query'] ?? '',
                    'bindings' => $data['bindings'] ?? [],
                    'time' => (float) ($data['time'] ?? 0), // Ensure it's a float
                    'timestamp' => $queryTimestamp,
                    'memory_delta' => $memoryDelta,
                ];

                $this->log(
                    "Query executed in {$data['time']}ms",
                    [
                        'query' => substr($data['query'] ?? '', 0, 100) . (strlen($data['query'] ?? '') > 100 ? '...' : ''),
                        'bindings' => $data['bindings'] ?? [],
                        'memory_impact' => $this->formatBytes($memoryDelta)
                    ],
                    'info',
                    'Database'
                );
            }
        ]);

        // Template rendering events
        event([
            'app:bladeTemplateRendered' => function ($templateData) {
                if (!$this->isCollectingData)
                    return;

                $renderTimestamp = microtime(true);

                $memoryBefore = $templateData['memory_before'] ?? (int) memory_get_usage(true);
                $memoryAfter = (int) memory_get_usage(true);

                $this->viewsRendered[] = [
                    'template' => $templateData['path'],
                    'render_time' => $templateData['time'] ?? 0,
                    'timestamp' => $renderTimestamp,
                    'memory_delta' => $memoryBefore - $memoryAfter
                ];

                // Only log the view render, don't take memory snapshot for each view
                $this->log('Blade template rendered', ['template' => $templateData['path']], 'info', 'Views');
            }
        ]);

        // Translation events - minimalist implementation
        event([
            'app:translator.loadedLanguageFile' => function ($langFile) {
                if (!$this->isCollectingData)
                    return;

                $loadTimestamp = microtime(true);
                $memoryBefore = $langFile['memory_before'] ?? (int) memory_get_usage(true);
                $memoryAfter = (int) memory_get_usage(true);

                $this->languageFiles[] = [
                    'file' => $langFile['file'],
                    'load_time' => (float) ($langFile['load_time'] ?? 0),
                    'timestamp' => $loadTimestamp,
                    'memory_delta' => $memoryBefore - $memoryAfter
                ];
                $this->log('Language file loaded', $langFile, 'info', 'Translation');
            }
        ]);
    }

    /**
     * Start a named timer to measure code execution duration.
     * 
     * @param string $name The name of the timer
     * @return void
     */
    public function startTimer(string $name): void
    {
        $this->timers[$name] = [
            'start' => microtime(true),
            'memory_start' => (int) memory_get_usage(true)
        ];
    }

    /**
     * Stop a named timer and calculate its duration and memory impact.
     * 
     * @param string $name The name of the timer
     * @return float The duration in seconds, or 0 if timer not found
     */
    public function stopTimer(string $name): float
    {
        if (!isset($this->timers[$name])) {
            return 0.0;
        }

        $endTime = microtime(true);
        $endMemory = (int) memory_get_usage(true);
        $duration = $endTime - $this->timers[$name]['start'];

        $this->timers[$name]['end'] = $endTime;
        $this->timers[$name]['duration'] = $duration;
        $this->timers[$name]['memory_end'] = $endMemory;
        $this->timers[$name]['memory_delta'] = $endMemory - $this->timers[$name]['memory_start'];

        return $duration;
    }

    /**
     * Update current and peak memory usage properties
     * 
     * @return void
     */
    protected function updateMemoryState(): void
    {
        $this->currentMemoryUsage = (int) memory_get_usage(true);
        $this->peakMemoryUsage = (int) memory_get_peak_usage(true);
    }

    /**
     * Take a memory snapshot with a label at the current point in time
     * 
     * @param string $key Unique key for the snapshot
     * @param string $label Descriptive label for the snapshot
     * @return void
     */
    protected function takeMemorySnapshot(string $key, string $label): void
    {
        // Always update memory state when taking a snapshot
        $this->updateMemoryState();

        $this->memorySnapshots[$key] = [
            'usage' => $this->currentMemoryUsage,
            'peak' => $this->peakMemoryUsage,
            'timestamp' => microtime(true),
            'label' => $label
        ];

        // Check for significant memory growth and auto-capture if needed
        $this->checkMemoryThresholds();
    }

    /**
     * Automatically capture memory snapshots when crossing significant thresholds
     * 
     * @return void
     */
    protected function checkMemoryThresholds(): void
    {
        static $thresholdsCrossed = [];

        $memoryGrowth = $this->currentMemoryUsage - $this->appStartMemory;
        $thresholds = [
            5 * 1024 * 1024,   // 5MB
            10 * 1024 * 1024,  // 10MB
            20 * 1024 * 1024,  // 20MB
            50 * 1024 * 1024   // 50MB
        ];

        foreach ($thresholds as $threshold) {
            $thresholdMB = $threshold / (1024 * 1024);
            $thresholdKey = "threshold_{$thresholdMB}mb";

            if ($memoryGrowth >= $threshold && !isset($thresholdsCrossed[$thresholdKey])) {
                $this->memorySnapshots[$thresholdKey] = [
                    'usage' => $this->currentMemoryUsage,
                    'peak' => $this->peakMemoryUsage,
                    'timestamp' => microtime(true),
                    'label' => "Memory Threshold: {$thresholdMB}MB Used"
                ];
                $thresholdsCrossed[$thresholdKey] = true;

                $this->log(
                    "Memory threshold crossed: {$thresholdMB}MB",
                    [
                        'current_memory' => $this->formatBytes($this->currentMemoryUsage),
                        'growth_from_start' => $this->formatBytes($memoryGrowth)
                    ],
                    'warning',
                    'Memory'
                );
            }
        }
    }

    /**
     * Logs a message with context, level, and optional grouping.
     * 
     * @param string $message The log message
     * @param array $context Additional context data
     * @param string $level Log level (e.g., info, warning, error)
     * @param string|null $group Optional group/category for the log
     * @return void
     */
    public function log(string $message, array $context = [], string $level = 'info', ?string $group = null): void
    {
        if (!$this->isCollectingData)
            return;

        $currentTime = microtime(true);
        $this->updateMemoryState();

        $log = [
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'timestamp' => $currentTime,
            'relative_time' => ($currentTime - $this->appStartTime) * 1000, // in milliseconds
            'memory' => $this->currentMemoryUsage,
            'peak_memory' => $this->peakMemoryUsage,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ];

        if ($group !== null) {
            if (!isset($this->logs[$group])) {
                $this->logs[$group] = [];
            }
            $this->logs[$group][] = $log;
            return;
        }

        $this->logs[] = $log;
    }

    /**
     * Gather all collected debug data into a structured array.
     * 
     * @return array The aggregated debug data
     */
    public function getDebugData(): array
    {
        return [
            'performance' => $this->getPerformanceData(),
            'database' => $this->getDatabaseData(),
            'views' => $this->getViewsData(),
            'translations' => $this->getTranslationData(),
            'request' => $this->getRequestData(),
            'environment' => $this->getEnvironmentData(),
            'logs' => $this->logs,
            'timeline' => $this->getTimelineData(),
            'memory' => $this->getMemoryData(),
            'timers' => $this->getTimersData()
        ];
    }

    /**
     * Record the current request's debug data to a JSON file for later analysis.
     * 
     * @return void
     */
    private function recordThisRequest(): void
    {
        $debugData = json_encode(['time' => time(), 'data' => $this->serializedDebugData($this->getDebugData())]);
        $tempDir = storage_dir('/temp/debugbar');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = $tempDir . '/snapshot_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.json';
        file_put_contents($filename, $debugData);

        // Removed old snapshots, keep only latest only
        $files = glob("$tempDir/snapshot_*.json");
        if (count($files) > $this->options['max_records']) {
            array_map('unlink', array_slice($files, 0, count($files) - $this->options['max_records']));
        }
    }

    /**
     * Recursively serialize debug data to ensure all objects are converted to arrays or strings.
     * 
     * @param mixed $data The data to serialize
     * @return mixed The serialized data
     */
    private function serializedDebugData($data)
    {
        // If it's an object that knows how to cast itself to array, do it and recurse
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof \Stringable) {
            return (string) $data;
        } elseif ($data instanceof Jsonable) {
            return $data->toJson();
        } elseif (is_object($data)) {
            // For generic objects, convert to array of public properties
            $data = get_object_vars($data);
        }

        // If it's an array, recurse into each element
        if (is_array($data)) {
            return array_map(
                /** @param mixed $item */
                fn($item): mixed => $this->serializedDebugData($item),
                $data
            );
        }

        // Otherwise return as-is (string/int/etc)
        return $data;
    }

    /**
     * Generate a JSON snapshot of the debug data for API responses.
     * 
     * @return array The debug data formatted for JSON output
     */
    public function getJsonSnapshot(): array
    {
        // Ensure we have accurate end values
        $this->appEndTime ??= microtime(true);
        $this->appEndMemory ??= (int) memory_get_usage(true);
        $this->appPeakMemory ??= (int) memory_get_peak_usage(true);

        $executionTime = ($this->appEndTime - $this->appStartTime) * 1000;
        $requestMemoryUsed = max(0, $this->appEndMemory - $this->appStartMemory);
        $requestPeakMemory = max(0, $this->appPeakMemory - $this->appStartMemory);

        // Fix: Proper database metrics calculation
        $queryTimes = array_filter(array_column($this->queries, 'time'), 'is_numeric');
        $totalQueryTime = array_sum($queryTimes);
        $avgQueryTime = count($queryTimes) > 0 ? $totalQueryTime / count($queryTimes) : 0;
        $slowestQuery = count($queryTimes) > 0 ? max($queryTimes) : 0;
        $fastestQuery = count($queryTimes) > 0 ? min($queryTimes) : 0;

        // Enhanced query analysis
        $queryTypes = [];
        $duplicateQueries = [];
        $queryHashes = [];
        $totalMemoryImpact = 0;

        foreach ($this->queries as $query) {
            $sql = trim($query['query'] ?? '');
            if (empty($sql))
                continue;

            $type = strtoupper(explode(' ', $sql)[0] ?? 'UNKNOWN');
            $queryTime = (float) ($query['time'] ?? 0);
            $memoryDelta = (int) ($query['memory_delta'] ?? 0);

            $totalMemoryImpact += $memoryDelta;

            // Query type analysis
            if (!isset($queryTypes[$type])) {
                $queryTypes[$type] = [
                    'count' => 0,
                    'total_time' => 0,
                    'avg_time' => 0,
                    'memory_impact' => 0
                ];
            }

            $queryTypes[$type]['count']++;
            $queryTypes[$type]['total_time'] += $queryTime;
            $queryTypes[$type]['memory_impact'] += $memoryDelta;
            $queryTypes[$type]['avg_time'] = $queryTypes[$type]['total_time'] / $queryTypes[$type]['count'];

            // Duplicate detection
            $normalizedQuery = preg_replace('/\s+/', ' ', trim($sql));
            $queryHash = md5($normalizedQuery);

            if (!isset($queryHashes[$queryHash])) {
                $queryHashes[$queryHash] = [
                    'query' => strlen($normalizedQuery) > 100 ? substr($normalizedQuery, 0, 100) . '...' : $normalizedQuery,
                    'count' => 0,
                    'total_time' => 0,
                    'total_memory' => 0
                ];
            }

            $queryHashes[$queryHash]['count']++;
            $queryHashes[$queryHash]['total_time'] += $queryTime;
            $queryHashes[$queryHash]['total_memory'] += $memoryDelta;
        }

        // Find actual duplicates
        foreach ($queryHashes as $data) {
            if ($data['count'] > 1) {
                $duplicateQueries[] = [
                    'query' => $data['query'],
                    'executions' => $data['count'],
                    'total_time' => round($data['total_time'], 3),
                    'avg_time' => round($data['total_time'] / $data['count'], 3),
                    'memory_impact' => $this->formatBytes($data['total_memory'])
                ];
            }
        }

        usort($duplicateQueries, fn($a, $b) => $b['executions'] <=> $a['executions']);

        // Calculate accurate database score
        $dbScore = $this->calculateDatabaseScore($executionTime, $totalQueryTime, count($this->queries), count($duplicateQueries), $avgQueryTime);

        $routeInfo = $this->routeInfo;
        unset($routeInfo['callback']); // Remove callback for security

        return [
            'timestamp' => date('Y-m-d H:i:s.u'),
            'execution_time' => round($executionTime, 2) . 'ms',
            'memory_used' => $this->formatBytes($requestMemoryUsed),
            'peak_memory' => $this->formatBytes($requestPeakMemory),
            'memory_efficiency' => $requestPeakMemory > 0 && $requestMemoryUsed > 0 ?
                round(($requestMemoryUsed / $requestPeakMemory) * 100, 1) : 100,

            'database' => [
                'query_count' => count($this->queries),
                'total_time' => round($totalQueryTime, 2) . 'ms',
                'average_time' => round($avgQueryTime, 3) . 'ms',
                'slowest_time' => round($slowestQuery, 3) . 'ms',
                'fastest_time' => round($fastestQuery, 3) . 'ms',
                'db_time_percentage' => $executionTime > 0 ? round(($totalQueryTime / $executionTime) * 100, 1) : 0,
                'performance_score' => $dbScore,
                'queries_per_second' => $executionTime > 0 ? round(count($this->queries) / ($executionTime / 1000), 2) : 0,
                'query_types' => $queryTypes,
                'duplicate_queries' => array_slice($duplicateQueries, 0, 10),
                'slowest_queries' => $this->getTopSlowQueries(5),
                'has_performance_issues' => $dbScore < 70,
                'total_memory_impact' => $this->formatBytes($totalMemoryImpact),
            ],

            'request_info' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                'url' => $_SERVER['REQUEST_URI'] ?? 'N/A',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                'ip' => $this->getClientIp(),
                'route' => $routeInfo,
                'middlewares' => $this->middlewares ?? []
            ],

            'alerts' => $this->getPerformanceAlerts($executionTime, $requestMemoryUsed, $totalQueryTime, count($this->queries)),

            'summary' => [
                'status' => $this->getOverallStatus($executionTime, $requestMemoryUsed, $dbScore),
                'memory_usage' => $this->getMemoryUsagePercentage() . '%',
                'memory_limit' => ini_get('memory_limit'),
                'total_queries' => count($this->queries),
                'php_version' => PHP_VERSION,
            ]
        ];
    }

    /**
     * Disable data collection
     * 
     * @return void
     */
    public function disable(): void
    {
        $this->isCollectingData = false;
    }

    /**
     * Enable data collection
     * 
     * @return void
     */
    public function enable(): void
    {
        $this->isCollectingData = true;
    }


    // ---- Individual data section generators ---- //
    protected function getPerformanceData(): array
    {
        // Ensure we have end time, if not set it now
        $this->appEndTime ??= microtime(true);

        // Ensure we have captured memory values
        $this->appEndMemory ??= (int) memory_get_usage(true);
        $this->appPeakMemory ??= (int) memory_get_peak_usage(true);

        $executionTime = $this->appEndTime - $this->appStartTime;

        // Use captured memory values for consistent calculations
        $currentMemory = $this->appEndMemory;
        $peakMemory = $this->appPeakMemory;
        $memoryUsedByRequest = max(0, $currentMemory - $this->appStartMemory);
        $peakMemoryUsedByRequest = max(0, $peakMemory - $this->appStartMemory);

        return [
            'execution_time' => round($executionTime * 1000, 3), // in milliseconds
            'execution_time_seconds' => round($executionTime, 6),
            'peak_memory' => $this->formatBytes($peakMemory),
            'current_memory' => $this->formatBytes($currentMemory),
            'memory_used' => $this->formatBytes($memoryUsedByRequest),
            'request_peak_memory' => $this->formatBytes($peakMemoryUsedByRequest),
            'start_memory' => $this->formatBytes($this->appStartMemory),
            'peak_memory_bytes' => $peakMemory,
            'current_memory_bytes' => $currentMemory,
            'memory_used_bytes' => $memoryUsedByRequest,
            'request_peak_memory_bytes' => $peakMemoryUsedByRequest,
            'start_memory_bytes' => $this->appStartMemory,
            'php_version' => PHP_VERSION,
            'included_files_count' => count(get_included_files()),
            'memory_limit' => $this->parseMemoryLimit(ini_get('memory_limit')),
            'memory_usage_percentage' => $this->getMemoryUsagePercentage(),
            'request_memory_percentage' => $this->getRequestMemoryPercentage()
        ];
    }

    protected function getDatabaseData(): array
    {
        $queryTimes = array_column($this->queries, 'time');
        $totalTime = array_sum($queryTimes);
        $queryCount = count($this->queries);
        $totalMemoryDelta = array_sum(array_column($this->queries, 'memory_delta'));

        // Use proper execution time calculation
        $executionTimeSeconds = ($this->appEndTime ?? microtime(true)) - $this->appStartTime;

        // Calculate meaningful statistics
        $averageTime = $queryCount > 0 ? $totalTime / $queryCount : 0;
        $slowestTime = $queryCount > 0 ? max($queryTimes) : 0;
        $fastestTime = $queryCount > 0 ? min($queryTimes) : 0;
        $queriesPerSecond = $executionTimeSeconds > 0 ? $queryCount / $executionTimeSeconds : 0;

        return [
            'query_count' => $queryCount,
            'total_time' => round($totalTime, 3),
            'queries' => $this->queries,
            'average_time' => round($averageTime, 3),
            'slowest_query_time' => round($slowestTime, 3),
            'fastest_query_time' => round($fastestTime, 3),
            'total_memory_impact' => $this->formatBytes($totalMemoryDelta),
            'queries_per_second' => round($queriesPerSecond, 2),
        ];
    }

    protected function getViewsData(): array
    {
        $totalRenderTime = array_sum(array_column($this->viewsRendered, 'render_time'));

        return [
            'rendered_count' => count($this->viewsRendered),
            'templates' => $this->viewsRendered,
            'total_render_time' => round($totalRenderTime, 3),
            'average_render_time' => count($this->viewsRendered) > 0 ?
                round($totalRenderTime / count($this->viewsRendered), 3) : 0
        ];
    }

    protected function getTranslationData(): array
    {
        $totalLoadTime = 0;
        $fileCount = count($this->languageFiles);

        foreach ($this->languageFiles as $file) {
            $totalLoadTime += $file['load_time'] ?? 0;
        }

        return [
            'loaded_files_count' => $fileCount,
            'total_load_time' => round($totalLoadTime, 2),
            'average_load_time' => $fileCount > 0 ? round($totalLoadTime / $fileCount, 2) : 0,
            'files' => $this->languageFiles
        ];
    }

    protected function getMemoryData(): array
    {
        $memoryHistory = [];
        $previousUsage = $this->appStartMemory;

        foreach ($this->memorySnapshots as $key => $snapshot) {
            $deltaFromStart = $snapshot['usage'] - $this->appStartMemory;
            $deltaFromPrevious = $snapshot['usage'] - $previousUsage;

            $memoryHistory[] = [
                'label' => $snapshot['label'],
                'usage' => $snapshot['usage'],
                'usage_formatted' => $this->formatBytes($snapshot['usage']),
                'delta_from_start' => $deltaFromStart,
                'delta_from_start_formatted' => $this->formatBytes($deltaFromStart),
                'delta_from_previous' => $deltaFromPrevious,
                'delta_from_previous_formatted' => $this->formatBytes(abs($deltaFromPrevious)),
                'delta_direction' => $deltaFromPrevious >= 0 ? 'increase' : 'decrease',
                'peak' => $snapshot['peak'],
                'peak_formatted' => $this->formatBytes($snapshot['peak']),
                'timestamp' => $snapshot['timestamp'],
                'relative_time' => round(($snapshot['timestamp'] - $this->appStartTime) * 1000, 2)
            ];

            $previousUsage = $snapshot['usage'];
        }

        // Ensure proper memory calculations
        $currentMemory = $this->appEndMemory ?? (int) memory_get_usage(true);
        $peakMemory = $this->appPeakMemory ?? (int) memory_get_peak_usage(true);

        $totalRequestMemory = max(0, $currentMemory - $this->appStartMemory);
        $peakRequestMemory = max(0, $peakMemory - $this->appStartMemory);

        // Proper memory efficiency calculation
        $memoryEfficiency = 100;
        if ($peakRequestMemory > 0 && $totalRequestMemory > 0) {
            $memoryEfficiency = round(($totalRequestMemory / $peakRequestMemory) * 100, 1);
        }

        return [
            'snapshots' => $memoryHistory,
            'start_memory' => $this->formatBytes($this->appStartMemory),
            'peak_usage' => $this->formatBytes($peakMemory),
            'current_usage' => $this->formatBytes($currentMemory),
            'request_memory_used' => $this->formatBytes($totalRequestMemory),
            'request_peak_memory' => $this->formatBytes($peakRequestMemory),
            'memory_growth' => $this->formatBytes($totalRequestMemory),
            'memory_efficiency' => $memoryEfficiency,
            'request_memory_bytes' => $totalRequestMemory,
            'request_peak_memory_bytes' => $peakRequestMemory,
        ];
    }

    protected function getTimersData(): array
    {
        $formattedTimers = [];
        foreach ($this->timers as $name => $timer) {
            $formattedTimers[$name] = [
                'duration' => isset($timer['duration']) ? round($timer['duration'] * 1000, 3) : null,
                'memory_delta' => isset($timer['memory_delta']) ? $this->formatBytes($timer['memory_delta']) : null,
                'start_time' => round(($timer['start'] - $this->appStartTime) * 1000, 2),
                'end_time' => isset($timer['end']) ? round(($timer['end'] - $this->appStartTime) * 1000, 2) : null
            ];
        }

        return $formattedTimers;
    }

    protected function getRequestData(): array
    {
        return [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'url' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'full_url' => $this->getCurrentUrl(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'ip' => $this->getClientIp(),
            'headers' => $this->getAllHeaders(),
            'get' => $_GET,
            'post' => $this->sanitizePostData($_POST),
            'cookies' => $_COOKIE,
            'session' => $this->sanitizeSessionData($_SESSION ?? []),
            'route_info' => $this->routeInfo,
            'middlewares' => $this->middlewares
        ];
    }

    protected function getEnvironmentData(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'localhost',
            'loaded_extensions' => get_loaded_extensions(),
            'include_path' => get_include_path(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_input_vars' => ini_get('max_input_vars'),
            'opcache_enabled' => extension_loaded('opcache') && function_exists('opcache_get_status') && opcache_get_status() !== false
        ];
    }

    protected function getTimelineData(): array
    {
        $timeline = [];
        $startTime = $this->appStartTime;

        // Add timer events
        foreach ($this->timers as $name => $timer) {
            if (isset($timer['duration'])) {
                $timeline[] = [
                    'name' => ucfirst(str_replace('_', ' ', $name)),
                    'group' => 'Timers',
                    'start' => round(($timer['start'] - $startTime) * 1000, 2),
                    'duration' => round($timer['duration'] * 1000, 2),
                    'color' => '#3498db'
                ];
            }
        }

        // Add application events from grouped logs
        foreach ($this->logs as $group => $logs) {
            if (is_string($group) && is_array($logs)) {
                foreach ($logs as $log) {
                    $timeline[] = [
                        'name' => $log['message'],
                        'group' => $group,
                        'start' => round($log['relative_time'], 2),
                        'duration' => 0.1, // Minimal duration for events
                        'color' => $this->getGroupColor($group)
                    ];
                }
            }
        }

        // Add database queries
        foreach ($this->queries as $index => $query) {
            $timeline[] = [
                'name' => "Query #" . ($index + 1),
                'group' => 'Database',
                'start' => round(($query['timestamp'] - $startTime) * 1000, 2),
                'duration' => $query['time'],
                'color' => '#e74c3c',
                'details' => substr($query['query'], 0, 100) . (strlen($query['query']) > 100 ? '...' : '')
            ];
        }

        // Sort timeline by start time
        usort($timeline, fn($a, $b) => $a['start'] <=> $b['start']);

        return $timeline;
    }

    protected function getGroupColor(string $group): string
    {
        $colors = [
            'Application' => '#3498db',
            'Routing' => '#2ecc71',
            'Middleware' => '#f39c12',
            'Database' => '#e74c3c',
            'Views' => '#9b59b6',
            'Translation' => '#1abc9c',
            'Timers' => '#34495e'
        ];

        return $colors[$group] ?? '#95a5a6';
    }

    private function calculateDatabaseScore(float $executionTime, float $totalQueryTime, int $queryCount, int $duplicateCount, float $avgQueryTime): int
    {
        $score = 100;

        // Query count penalty
        if ($queryCount > 100)
            $score -= 30;
        elseif ($queryCount > 50)
            $score -= 20;
        elseif ($queryCount > 25)
            $score -= 10;

        // Average query time penalty
        if ($avgQueryTime > 100)
            $score -= 25;
        elseif ($avgQueryTime > 50)
            $score -= 15;
        elseif ($avgQueryTime > 25)
            $score -= 10;

        // Total query time vs execution time ratio penalty
        $dbTimeRatio = $executionTime > 0 ? ($totalQueryTime / $executionTime) : 0;
        if ($dbTimeRatio > 0.7)
            $score -= 25;
        elseif ($dbTimeRatio > 0.5)
            $score -= 15;
        elseif ($dbTimeRatio > 0.3)
            $score -= 10;

        // Duplicate queries penalty
        if ($duplicateCount > 10)
            $score -= 20;
        elseif ($duplicateCount > 5)
            $score -= 15;
        elseif ($duplicateCount > 0)
            $score -= 10;

        return max(0, min(100, $score));
    }

    protected function getTopSlowQueries(int $limit = 5): array
    {
        $sortedQueries = $this->queries;
        usort($sortedQueries, fn($a, $b) => ($b['time'] ?? 0) <=> ($a['time'] ?? 0));

        $slowQueries = [];
        foreach (array_slice($sortedQueries, 0, $limit) as $query) {
            $queryText = trim($query['query'] ?? '');
            $slowQueries[] = [
                'query' => strlen($queryText) > 150 ? substr($queryText, 0, 150) . '...' : $queryText,
                'time' => round((float) ($query['time'] ?? 0), 3),
                'bindings' => $query['bindings'] ?? [],
                'memory_delta' => $this->formatBytes($query['memory_delta'] ?? 0),
            ];
        }

        return $slowQueries;
    }

    private function getPerformanceAlerts(float $executionTime, int $memoryUsed, float $totalQueryTime, int $queryCount): array
    {
        $alerts = [];

        // Execution time alerts
        if ($executionTime > 2000) {
            $alerts[] = [
                'type' => 'critical',
                'category' => 'performance',
                'message' => 'Very slow response time (' . round($executionTime, 2) . 'ms)',
                'suggestion' => 'Consider optimizing database queries or caching'
            ];
        } elseif ($executionTime > 1000) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'performance',
                'message' => 'Slow response time (' . round($executionTime, 2) . 'ms)',
                'suggestion' => 'Review query performance and consider optimization'
            ];
        }

        // Memory alerts
        if ($memoryUsed > 50 * 1024 * 1024) { // 50MB
            $alerts[] = [
                'type' => 'critical',
                'category' => 'memory',
                'message' => 'High memory usage (' . $this->formatBytes($memoryUsed) . ')',
                'suggestion' => 'Check for memory leaks or large data processing'
            ];
        } elseif ($memoryUsed > 20 * 1024 * 1024) { // 20MB
            $alerts[] = [
                'type' => 'warning',
                'category' => 'memory',
                'message' => 'Elevated memory usage (' . $this->formatBytes($memoryUsed) . ')',
                'suggestion' => 'Monitor memory consumption patterns'
            ];
        }

        // Database alerts
        if ($queryCount > 100) {
            $alerts[] = [
                'type' => 'critical',
                'category' => 'database',
                'message' => 'Too many database queries (' . $queryCount . ')',
                'suggestion' => 'Consider query optimization, eager loading, or caching'
            ];
        } elseif ($queryCount > 50) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'database',
                'message' => 'High number of database queries (' . $queryCount . ')',
                'suggestion' => 'Review N+1 query problems and consider optimization'
            ];
        }

        if ($totalQueryTime > ($executionTime * 0.5)) {
            $alerts[] = [
                'type' => 'critical',
                'category' => 'database',
                'message' => 'Database queries consuming ' . round(($totalQueryTime / $executionTime) * 100, 1) . '% of response time',
                'suggestion' => 'Optimize slow queries or add database indexes'
            ];
        }

        return $alerts;
    }

    private function getOverallStatus(float $executionTime, int $memoryUsed, int $dbScore): string
    {
        $score = 100;

        // Deduct points based on performance
        if ($executionTime > 2000) {
            $score -= 40;
        } elseif ($executionTime > 1000) {
            $score -= 25;
        } elseif ($executionTime > 500) {
            $score -= 15;
        }

        if ($memoryUsed > 50 * 1024 * 1024) {
            $score -= 30;
        } elseif ($memoryUsed > 20 * 1024 * 1024) {
            $score -= 15;
        }

        $score -= (100 - $dbScore) * 0.3; // Database score impact

        $score = max(0, min(100, $score));

        if ($score >= 90)
            return 'excellent';
        if ($score >= 75)
            return 'good';
        if ($score >= 60)
            return 'fair';
        if ($score >= 40)
            return 'poor';
        return 'critical';
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $absBytes = abs($bytes);
        $sign = $bytes < 0 ? '-' : '';

        if ($absBytes == 0) {
            return '0 B';
        }

        $pow = floor(log($absBytes) / log(1024));
        $pow = min($pow, count($units) - 1);

        $value = $absBytes / (1 << (10 * $pow));

        return $sign . round($value, 2) . ' ' . $units[$pow];
    }

    private function parseMemoryLimit(string $memoryLimit): int
    {
        if ($memoryLimit === '-1') {
            return -1; // Unlimited
        }

        $value = (int) $memoryLimit;
        $unit = strtoupper(substr($memoryLimit, -1));

        switch ($unit) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }

        return $value;
    }

    private function getMemoryUsagePercentage(): float
    {
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        if ($memoryLimit === -1) {
            return 0.0;
        }

        $currentUsage = $this->appEndMemory ?: (int) memory_get_usage(true);
        return round(($currentUsage / $memoryLimit) * 100, 2);

    }

    private function getRequestMemoryPercentage(): float
    {
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        if ($memoryLimit === -1) {
            return 0.0;
        }

        $currentUsage = $this->appEndMemory ?: (int) memory_get_usage(true);
        $requestMemory = max(0, $currentUsage - $this->appStartMemory);
        return round(($requestMemory / $memoryLimit) * 100, 2);
    }

    private function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return "$protocol://$host$uri";
    }

    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return '127.0.0.1';
    }

    private function getAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    private function sanitizePostData(array $data): array
    {
        $sensitiveFields = ['password', 'token', 'secret', 'key', 'pass', 'pwd', 'auth'];
        return $this->recursiveSanitize($data, $sensitiveFields);
    }

    private function sanitizeSessionData(array $data): array
    {
        $sensitiveFields = ['password', 'token', 'secret', 'key', 'auth', 'csrf'];
        return $this->recursiveSanitize($data, $sensitiveFields);
    }

    private function recursiveSanitize(array $data, array $sensitiveFields): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $keyLower = strtolower($key);
            $shouldHide = false;

            foreach ($sensitiveFields as $field) {
                if (str_contains($keyLower, $field)) {
                    $shouldHide = true;
                    break;
                }
            }

            if ($shouldHide) {
                $sanitized[$key] = '[HIDDEN]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->recursiveSanitize($value, $sensitiveFields);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
