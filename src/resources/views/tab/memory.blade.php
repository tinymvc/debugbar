<div id="memory" class="debugbar-panel">
    <div class="debugbar-panel-content">
        <div class="debugbar-section">
            <h3 class="debugbar-section-title">Memory Usage</h3>

            <div class="debugbar-grid">
                <div class="debugbar-card">
                    <div class="debugbar-card-title">Request Memory Used</div>
                    <div
                        class="debugbar-card-value {{ ($memory['request_memory_used'] ?? '0 B') === '0 B' ? 'performance-good' : 'performance-warning' }}">
                        {{ $memory['request_memory_used'] ?? '0 B' }}
                    </div>
                    <div class="debugbar-card-subtitle">Memory consumed by this request</div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Request Peak Memory</div>
                    <div class="debugbar-card-value">{{ $memory['request_peak_memory'] ?? '0 B' }}</div>
                    <div class="debugbar-card-subtitle">Highest memory during request</div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Memory Efficiency</div>
                    <div
                        class="debugbar-card-value {{ ($memory['memory_efficiency'] ?? 0) < 50 ? 'performance-danger' : (($memory['memory_efficiency'] ?? 0) < 75 ? 'performance-warning' : 'performance-good') }}">
                        {{ number_format($memory['memory_efficiency'] ?? 0, 1) }}%
                    </div>
                    <div class="debugbar-card-subtitle">Current/peak efficiency ratio</div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Starting Memory</div>
                    <div class="debugbar-card-value" style="font-size: 16px;">
                        {{ $memory['start_memory'] ?? '0 B' }}</div>
                    <div class="debugbar-card-subtitle">Memory before request</div>
                </div>
            </div>

            @if (isset($memory['snapshots']) && count($memory['snapshots']) > 1)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Memory Snapshots
                    ({{ count($memory['snapshots']) }})</h4>
                <div class="debugbar-collapsible-content">
                    <table class="debugbar-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Memory Delta</th>
                                <th>Step Change</th>
                                <th>Total Growth</th>
                                <th>Time (ms)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($memory['snapshots'] as $snapshot)
                                <tr>
                                    <td><strong>{{ $snapshot['label'] }}</strong></td>
                                    <td class="performance-good">
                                        <strong>+{{ $snapshot['delta_from_start_formatted'] }}</strong>
                                    </td>
                                    <td
                                        class="{{ $snapshot['delta_from_previous'] > 0 ? 'performance-warning' : 'performance-good' }}">
                                        <strong>{{ $snapshot['delta_from_previous'] >= 0 ? '+' : '' }}{{ $snapshot['delta_from_previous_formatted'] }}</strong>
                                    </td>
                                    <td
                                        class="{{ $snapshot['delta_from_start'] > 1048576 ? 'performance-danger' : ($snapshot['delta_from_start'] > 524288 ? 'performance-warning' : 'performance-good') }}">
                                        <strong>+{{ $snapshot['delta_from_start_formatted'] }}</strong>
                                    </td>
                                    <td>{{ number_format($snapshot['relative_time'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Request Memory Growth Chart
                </h4>
                <div class="debugbar-collapsible-content">
                    <div
                        style="background: var(--debugbar-light); padding: 20px; border-radius: 8px; margin-top: 16px;">
                        @php
                            $maxDelta = 0;
                            foreach ($memory['snapshots'] as $snapshot) {
                                if ($snapshot['delta_from_start'] > $maxDelta) {
                                    $maxDelta = $snapshot['delta_from_start'];
                                }
                            }
                            $maxDelta = max($maxDelta, 1024); // Minimum 1KB for visualization
                        @endphp

                        @foreach ($memory['snapshots'] as $index => $snapshot)
                            <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                <div style="width: 120px; font-size: 11px; color: var(--debugbar-text-light);">
                                    {{ $snapshot['label'] }}
                                </div>
                                <div
                                    style="flex: 1; background: #e2e8f0; height: 20px; border-radius: 10px; margin: 0 12px; position: relative;">
                                    <div
                                        style="background: linear-gradient(90deg, 
                                                    {{ $snapshot['delta_from_start'] > 1048576 ? '#ef4444' : ($snapshot['delta_from_start'] > 524288 ? '#f59e0b' : '#10b981') }}, 
                                                    {{ $snapshot['delta_from_start'] > 1048576 ? '#dc2626' : ($snapshot['delta_from_start'] > 524288 ? '#d97706' : '#059669') }}); 
                                                        height: 100%; 
                                                        width: {{ ($snapshot['delta_from_start'] / $maxDelta) * 100 }}%; 
                                                        border-radius: 10px; 
                                                        transition: width 0.3s ease;">
                                    </div>
                                </div>
                                <div style="width: 80px; font-size: 11px; text-align: right; font-family: monospace;">
                                    +{{ $snapshot['delta_from_start_formatted'] }}
                                </div>
                            </div>
                        @endforeach

                        <div
                            style="margin-top: 16px; padding: 12px; background: white; border-radius: 6px; font-size: 11px; color: var(--debugbar-text-light);">
                            <strong>Chart shows:</strong> Memory added by request at each step (not total system
                            memory)
                        </div>
                    </div>
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: var(--debugbar-text-muted);">
                    <div style="font-size: 48px; margin-bottom: 16px;">🧠</div>
                    <p>No memory snapshots were recorded during this request.</p>
                    <p style="font-size: 12px; color: var(--debugbar-text-light);">Use
                        <code>DebugBar::captureMemory('label')</code> to add manual snapshots.</p>
                </div>
            @endif
        </div>
    </div>
</div>
