<div id="performance" class="debugbar-panel active">
    <div class="debugbar-panel-content">
        <div class="debugbar-section">
            <h3 class="debugbar-section-title">Performance Metrics</h3>

            <div class="debugbar-grid">
                <div class="debugbar-card">
                    <div class="debugbar-card-title">Execution Time</div>
                    <div class="debugbar-card-value {{ __getPerformanceClass($performance['execution_time'] ?? 0) }}">
                        {{ number_format($performance['execution_time'] ?? 0, 2) }}ms
                    </div>
                    <div class="debugbar-card-subtitle">
                        {{ number_format($performance['execution_time_seconds'] ?? 0, 6) }} seconds
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Request Peak Memory</div>
                    <div class="debugbar-card-value">{{ $performance['request_peak_memory'] ?? '0 B' }}</div>
                    <div class="debugbar-card-subtitle">
                        Highest memory used by request
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Request Memory Used</div>
                    <div
                        class="debugbar-card-value {{ ($performance['memory_used'] ?? '0 B') === '0 B' ? 'performance-good' : 'performance-warning' }}">
                        {{ $performance['memory_used'] ?? '0 B' }}</div>
                    <div class="debugbar-card-subtitle">
                        {{ number_format($performance['request_memory_percentage'] ?? 0, 2) }}% of limit
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Included Files</div>
                    <div class="debugbar-card-value">{{ $performance['included_files_count'] ?? 0 }}</div>
                    <div class="debugbar-card-subtitle">
                        PHP {{ $performance['php_version'] ?? 'Unknown' }}
                    </div>
                </div>
            </div>

            @if (isset($timers) && count($timers) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Application Timers</h4>
                <div class="debugbar-collapsible-content">
                    @foreach ($timers as $name => $timer)
                        <div class="debugbar-metric">
                            <span class="debugbar-metric-label">{{ ucfirst(str_replace('_', ' ', $name)) }}</span>
                            <span class="debugbar-metric-value">
                                {{ $timer['duration'] ?? 'N/A' }}{{ isset($timer['duration']) ? 'ms' : '' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
