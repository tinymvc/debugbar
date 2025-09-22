@php
    function __getPerformanceClass(float $time): string
    {
        if ($time > 1000) {
            return 'performance-danger';
        }
        if ($time > 500) {
            return 'performance-warning';
        }
        return 'performance-good';
    }

    function __getTotalLogCount(array $logs): int
    {
        $total = 0;
        foreach ($logs as $group) {
            if (is_array($group)) {
                $total += count($group);
            }
        }
        return $total;
    }

    function __formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = abs($bytes);
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
@endphp

@include('inc.styles')

<div id="tinymvc-debugbar" class="minimized">

    <div class="debugbar-header" onclick="toggleDebugBar()">
        <div class="debugbar-title">
            <div class="debugbar-logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-bug-icon lucide-bug">
                    <path d="M12 20v-9" />
                    <path d="M14 7a4 4 0 0 1 4 4v3a6 6 0 0 1-12 0v-3a4 4 0 0 1 4-4z" />
                    <path d="M14.12 3.88 16 2" />
                    <path d="M21 21a4 4 0 0 0-3.81-4" />
                    <path d="M21 5a4 4 0 0 1-3.55 3.97" />
                    <path d="M22 13h-4" />
                    <path d="M3 21a4 4 0 0 1 3.81-4" />
                    <path d="M3 5a4 4 0 0 0 3.55 3.97" />
                    <path d="M6 13H2" />
                    <path d="m8 2 1.88 1.88" />
                    <path d="M9 7.13V6a3 3 0 1 1 6 0v1.13" />
                </svg>
            </div>
            <span>Debug Bar</span>
        </div>

        <div class="debugbar-summary">
            <div class="debugbar-summary-item">
                <span>⚡</span>
                <span class="{{ __getPerformanceClass($performance['execution_time'] ?? 0) }}">
                    {{ number_format($performance['execution_time'] ?? 0, 2) }}ms
                </span>
            </div>
            <div class="debugbar-summary-item">
                <span>🧠</span>
                <span>{{ $performance['memory_used'] ?? '0 B' }}</span>
            </div>
            <div class="debugbar-summary-item">
                <span>🗄️</span>
                <span
                    class="{{ ($database['query_count'] ?? 0) > 20 ? 'performance-danger' : (($database['query_count'] ?? 0) > 10 ? 'performance-warning' : 'performance-good') }}">
                    {{ $database['query_count'] ?? 0 }} queries
                </span>
            </div>
            <div class="debugbar-summary-item">
                <span>🎨</span>
                <span>{{ $views['rendered_count'] ?? 0 }} views</span>
            </div>
        </div>

        <button class="debugbar-toggle" onclick="event.stopPropagation(); toggleDebugBar()">
            <span id="debugbar-toggle-icon">▼</span>
        </button>
    </div>

    <div class="debugbar-content">
        <div class="debugbar-tabs">
            <div class="debugbar-tab active" onclick="showPanel('performance')">
                ⚡ Performance
                <span class="debugbar-badge">{{ number_format($performance['execution_time'] ?? 0, 2) }}ms</span>
            </div>

            <div class="debugbar-tab {{ ($database['query_count'] ?? 0) > 20 ? 'danger' : (($database['query_count'] ?? 0) > 10 ? 'warning' : '') }}"
                onclick="showPanel('database')">
                🗄️ Database
                <span class="debugbar-badge">{{ $database['query_count'] ?? 0 }}</span>
            </div>

            <div class="debugbar-tab" onclick="showPanel('views')">
                🎨 Views
                <span class="debugbar-badge">{{ $views['rendered_count'] ?? 0 }}</span>
            </div>

            <div class="debugbar-tab" onclick="showPanel('request')">
                🌐 Request
            </div>

            <div class="debugbar-tab" onclick="showPanel('timeline')">
                📊 Timeline
            </div>

            <div class="debugbar-tab" onclick="showPanel('memory')">
                🧠 Memory
                <span class="debugbar-badge">{{ $performance['memory_used'] ?? '0 B' }}</span>
            </div>

            <div class="debugbar-tab" onclick="showPanel('logs')">
                📝 Logs
                <span class="debugbar-badge">{{ __getTotalLogCount($logs) }}</span>
            </div>

            <div class="debugbar-tab" onclick="showPanel('environment')">
                ⚙️ Environment
            </div>
        </div>

        <!-- Performance Panel -->
        @include('tab.performance')

        <!-- Database Panel -->
        @include('tab.database')

        <!-- Views Panel -->
        @include('tab.views')

        <!-- Request Panel -->
        @include('tab.request')

        <!-- Timeline Panel -->
        @include('tab.timeline')

        <!-- Memory Panel -->
        @include('tab.memory')

        <!-- Logs Panel -->
        @include('tab.logs')

        <!-- Environment Panel -->
        @include('tab.environment')
    </div>
</div>

@include('inc.scripts')
