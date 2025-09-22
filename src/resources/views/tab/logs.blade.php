<div id="logs" class="debugbar-panel">
    <div class="debugbar-panel-content">
        <div class="debugbar-section">
            <h3 class="debugbar-section-title">Application Logs</h3>

            @if (isset($logs) && count($logs) > 0)
                @php
                    $totalLogs = __getTotalLogCount($logs);
                @endphp

                <div class="debugbar-grid">
                    <div class="debugbar-card">
                        <div class="debugbar-card-title">Total Log Entries</div>
                        <div class="debugbar-card-value">{{ $totalLogs }}</div>
                    </div>

                    <div class="debugbar-card">
                        <div class="debugbar-card-title">Log Groups</div>
                        <div class="debugbar-card-value">{{ count($logs) }}</div>
                    </div>
                </div>

                @foreach ($logs as $group => $groupLogs)
                    @if (is_array($groupLogs) && count($groupLogs) > 0)
                        <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">
                            {{ $group }} ({{ count($groupLogs) }} entries)
                        </h4>
                        <div class="debugbar-collapsible-content">
                            @foreach ($groupLogs as $log)
                                <div class="debugbar-log-entry {{ $log['level'] ?? 'info' }}">
                                    <div class="debugbar-log-message">{{ $log['message'] ?? 'No message' }}
                                    </div>
                                    <div class="debugbar-log-meta">
                                        <span>
                                            <span
                                                class="debugbar-status-indicator status-{{ $log['level'] ?? 'info' }}"></span>
                                            {{ strtoupper($log['level'] ?? 'INFO') }}
                                        </span>
                                        <span>{{ number_format($log['relative_time'] ?? 0, 2) }}ms</span>
                                        <span>{{ isset($log['memory']) ? __formatBytes($log['memory']) : 'N/A' }}</span>
                                    </div>

                                    @if (isset($log['context']) && count($log['context']) > 0)
                                        <div class="debugbar-collapsible collapsed" onclick="toggleCollapsible(this)"
                                            style="font-size: 11px;">
                                            Show Context
                                        </div>
                                        <div class="debugbar-collapsible-content">
                                            @php
                                                echo '<div class="debugbar-code">';
                                                echo json_encode(
                                                    $log['context'],
                                                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                                                );
                                                echo '</div>';
                                            @endphp
                                        </div>
                                    @endif

                                    @if (isset($log['trace']) && count($log['trace']) > 0)
                                        <div class="debugbar-collapsible collapsed" onclick="toggleCollapsible(this)"
                                            style="font-size: 11px;">
                                            Show Stack Trace
                                        </div>
                                        <div class="debugbar-collapsible-content">
                                            @php
                                                echo '<div class="debugbar-code">';
                                                foreach (array_slice($log['trace'], 0, 5) as $trace):
                                                    echo sprintf(
                                                        '<p>%s:%s &rarr; %s()</p>',
                                                        $trace['file'] ?? 'Unknown',
                                                        $trace['line'] ?? '?',
                                                        $trace['function'] ?? 'Unknown',
                                                    );
                                                endforeach;
                                                echo '</div>';
                                            @endphp
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            @else
                <div style="text-align: center; padding: 40px; color: var(--debugbar-text-muted);">
                    <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                    <p>No log entries were recorded.</p>
                </div>
            @endif
        </div>
    </div>
</div>
