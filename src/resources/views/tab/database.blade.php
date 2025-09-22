<div id="database" class="debugbar-panel">
    <div class="debugbar-panel-content">
        <div class="debugbar-section">
            <h3 class="debugbar-section-title">Database Performance</h3>

            <div class="debugbar-grid">
                <div class="debugbar-card">
                    <div class="debugbar-card-title">Total Queries</div>
                    <div
                        class="debugbar-card-value {{ ($database['query_count'] ?? 0) > 20 ? 'performance-danger' : (($database['query_count'] ?? 0) > 10 ? 'performance-warning' : 'performance-good') }}">
                        {{ $database['query_count'] ?? 0 }}
                    </div>
                    <div class="debugbar-card-subtitle">
                        {{ $database['queries_per_second'] ?? 0 }} queries/sec
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Total Time</div>
                    <div
                        class="debugbar-card-value {{ ($database['total_time'] ?? 0) > 1000 ? 'performance-danger' : (($database['total_time'] ?? 0) > 500 ? 'performance-warning' : 'performance-good') }}">
                        {{ number_format($database['total_time'] ?? 0, 2) }}ms
                    </div>
                    <div class="debugbar-card-subtitle">
                        Avg: {{ number_format($database['average_time'] ?? 0, 2) }}ms
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Slowest Query</div>
                    <div
                        class="debugbar-card-value {{ ($database['slowest_query_time'] ?? 0) > 100 ? 'performance-danger' : (($database['slowest_query_time'] ?? 0) > 50 ? 'performance-warning' : 'performance-good') }}">
                        {{ number_format($database['slowest_query_time'] ?? 0, 2) }}ms
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Memory Impact</div>
                    <div class="debugbar-card-value">{{ $database['total_memory_impact'] ?? '0 B' }}</div>
                </div>
            </div>

            @if (isset($database['queries']) && count($database['queries']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Query Details</h4>
                <div class="debugbar-collapsible-content">
                    <table class="debugbar-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Query</th>
                                <th>Time (ms)</th>
                                <th>Memory Δ</th>
                                <th>Bindings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($database['queries'] as $index => $query)
                                <tr>
                                    <td><strong>{{ $index + 1 }}</strong></td>
                                    <td>
                                        <div class="debugbar-code">{{ $query['query'] ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span
                                            class="{{ ($query['time'] ?? 0) > 100 ? 'performance-danger' : (($query['time'] ?? 0) > 50 ? 'performance-warning' : 'performance-good') }}">
                                            <strong>{{ number_format($query['time'] ?? 0, 3) }}</strong>
                                        </span>
                                    </td>
                                    <td>{{ isset($query['memory_delta']) ? __formatBytes($query['memory_delta']) : 'N/A' }}
                                    </td>
                                    <td>
                                        @if (isset($query['bindings']) && count($query['bindings']) > 0)
                                            @php
                                                echo '<div class="debugbar-code">';
                                                echo json_encode($query['bindings'], JSON_UNESCAPED_SLASHES);
                                                echo '</div>';
                                            @endphp
                                        @else
                                            <em>None</em>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: var(--debugbar-text-muted);">
                    <div style="font-size: 48px; margin-bottom: 16px;">🗄️</div>
                    <p>No database queries were executed.</p>
                </div>
            @endif
        </div>
    </div>
</div>
