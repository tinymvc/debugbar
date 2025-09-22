<div id="views" class="debugbar-panel">
    <div class="debugbar-panel-content">
        <div class="debugbar-section">
            <h3 class="debugbar-section-title">Template Rendering</h3>

            <div class="debugbar-grid">
                <div class="debugbar-card">
                    <div class="debugbar-card-title">Templates Rendered</div>
                    <div class="debugbar-card-value">{{ $views['rendered_count'] ?? 0 }}</div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Total Render Time</div>
                    <div
                        class="debugbar-card-value {{ ($views['total_render_time'] ?? 0) > 100 ? 'performance-danger' : (($views['total_render_time'] ?? 0) > 50 ? 'performance-warning' : 'performance-good') }}">
                        {{ number_format($views['total_render_time'] ?? 0, 2) }}ms
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Average Render Time</div>
                    <div
                        class="debugbar-card-value {{ ($views['average_render_time'] ?? 0) > 50 ? 'performance-danger' : (($views['average_render_time'] ?? 0) > 25 ? 'performance-warning' : 'performance-good') }}">
                        {{ number_format($views['average_render_time'] ?? 0, 2) }}ms
                    </div>
                </div>
            </div>

            @if (isset($views['templates']) && count($views['templates']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Template Details</h4>
                <div class="debugbar-collapsible-content">
                    <table class="debugbar-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Template</th>
                                <th>Render Time (ms)</th>
                                <th>Memory Usage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($views['templates'] as $index => $template)
                                <tr>
                                    <td><strong>{{ $index + 1 }}</strong></td>
                                    <td>
                                        <code>{{ $template['template'] ?? 'Unknown' }}</code>
                                    </td>
                                    <td>
                                        <span
                                            class="{{ ($template['render_time'] ?? 0) > 50 ? 'performance-danger' : (($template['render_time'] ?? 0) > 25 ? 'performance-warning' : 'performance-good') }}">
                                            <strong>{{ number_format($template['render_time'] ?? 0, 3) }}</strong>
                                        </span>
                                    </td>
                                    <td>{{ isset($template['memory_delta']) ? __formatBytes($template['memory_delta']) : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: var(--debugbar-text-muted);">
                    <div style="font-size: 48px; margin-bottom: 16px;">🎨</div>
                    <p>No templates were rendered.</p>
                </div>
            @endif
        </div>
    </div>
</div>
