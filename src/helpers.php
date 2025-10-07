<?php

use DebugBar\DebugBar;

if (!function_exists('debugbar')) {
    /**
     * Get the global debug bar instance
     *
     * @param array $options Optional configuration options
     *              - record: bool Whether to record the entire request snapshots
     *              - max_records: int Maximum number of request snapshots to keep
     *              - show_debugbar: bool Whether to enable the bottom debug bar
     *              - show_ajax: bool Whether to enable AJAX support for the debug bar
     * 
     * @return \DebugBar\DebugBar
     */
    function debugbar(array $options = []): DebugBar
    {
        static $instance = null;

        if ($instance === null) {
            $instance = DebugBar::register($options);
        }

        return $instance;
    }
}

if (!function_exists('debug_log')) {
    /**
     * Quick helper to log messages to the debug bar
     * 
     * @param string $message
     * @param array $context
     * @param string $level
     * @param string|null $group
     *
     * @return void
     */
    function debug_log(string $message, array $context = [], string $level = 'info', ?string $group = null): void
    {
        debugbar()->log($message, $context, $level, $group);
    }
}

if (!function_exists('debug_time_start')) {
    /**
     * Start timing an operation
     * 
     * @param mixed $name
     * @return void
     */
    function debug_time_start(string $name): void
    {
        $GLOBALS['debug_timers'][$name] = microtime(true);
    }
}

if (!function_exists('debug_time_end')) {
    /**
     * End timing an operation and log it
     * 
     * @param mixed $name
     * @return void
     */
    function debug_time_end(string $name): void
    {
        if (!isset($GLOBALS['debug_timers'][$name])) {
            debug_log("Timer '$name' was never started", [], 'warning', 'Timing');
            return;
        }

        $duration = round((microtime(true) - $GLOBALS['debug_timers'][$name]) * 1000, 2);
        debug_log("$name completed", ['duration' => $duration . 'ms'], 'info', 'Timing');

        unset($GLOBALS['debug_timers'][$name]);
    }
}

if (!function_exists('debug_memory')) {
    /**
     * Log current memory usage
     * 
     * @param string $label
     * @return mixed
     */
    function debug_memory(string $label = 'Memory Usage'): void
    {
        $current = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        debug_log($label, [
            'current' => number_format($current / 1024, 2) . ' KB',
            'peak' => number_format($peak / 1024, 2) . ' KB'
        ], 'info', 'Memory');
    }
}

if (!function_exists('debug_dump')) {
    /**
     * Dump variables to the debug bar
     * 
     * @param mixed $variable
     * @param string $label
     * @return void
     */
    function debug_dump(mixed $variable, string $label = 'Variable Dump'): void
    {
        debug_log($label, [
            'type' => gettype($variable),
            'value' => $variable,
            'json' => json_encode($variable, JSON_PRETTY_PRINT)
        ], 'info', 'Debug');
    }
}
