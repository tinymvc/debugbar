<div id="environment" class="debugbar-panel">
    <div class="debugbar-panel-content">
        <div class="debugbar-section">
            <h3 class="debugbar-section-title">Environment Information</h3>

            <div class="debugbar-grid">
                <div class="debugbar-card">
                    <div class="debugbar-card-title">PHP Version</div>
                    <div class="debugbar-card-value" style="font-size: 18px;">
                        {{ $environment['php_version'] ?? 'Unknown' }}</div>
                    <div class="debugbar-card-subtitle">{{ $environment['php_sapi'] ?? 'Unknown SAPI' }}
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Server Software</div>
                    <div class="debugbar-card-value" style="font-size: 16px;">
                        {{ $environment['server_software'] ?? 'Unknown' }}</div>
                    <div class="debugbar-card-subtitle">{{ $environment['server_name'] ?? 'localhost' }}
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Memory Limit</div>
                    <div class="debugbar-card-value" style="font-size: 18px;">
                        {{ $environment['memory_limit'] ?? 'Unknown' }}</div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Max Execution Time</div>
                    <div class="debugbar-card-value">{{ $environment['max_execution_time'] ?? 'Unknown' }}s
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">OPcache</div>
                    <div
                        class="debugbar-card-value {{ $environment['opcache_enabled'] ?? false ? 'performance-good' : 'performance-warning' }}">
                        {{ $environment['opcache_enabled'] ?? false ? 'Enabled' : 'Disabled' }}
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Document Root</div>
                    <div class="debugbar-card-value" style="font-size: 12px; word-break: break-all;">
                        {{ $environment['document_root'] ?? 'Unknown' }}</div>
                </div>
            </div>

            <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">PHP Configuration</h4>
            <div class="debugbar-collapsible-content">
                <div class="debugbar-grid">
                    <div class="debugbar-metric">
                        <span class="debugbar-metric-label">Post Max Size</span>
                        <span class="debugbar-metric-value">{{ $environment['post_max_size'] ?? 'Unknown' }}</span>
                    </div>
                    <div class="debugbar-metric">
                        <span class="debugbar-metric-label">Upload Max Filesize</span>
                        <span
                            class="debugbar-metric-value">{{ $environment['upload_max_filesize'] ?? 'Unknown' }}</span>
                    </div>
                    <div class="debugbar-metric">
                        <span class="debugbar-metric-label">Max Input Vars</span>
                        <span class="debugbar-metric-value">{{ $environment['max_input_vars'] ?? 'Unknown' }}</span>
                    </div>
                    <div class="debugbar-metric">
                        <span class="debugbar-metric-label">Include Path</span>
                        <span class="debugbar-metric-value"
                            style="font-size: 11px; word-break: break-all;">{{ $environment['include_path'] ?? 'Unknown' }}</span>
                    </div>
                </div>
            </div>

            @if (isset($environment['loaded_extensions']) && count($environment['loaded_extensions']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Loaded Extensions
                    ({{ count($environment['loaded_extensions']) }})</h4>
                <div class="debugbar-collapsible-content">
                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; margin-top: 16px;">
                        @foreach ($environment['loaded_extensions'] as $extension)
                            <div
                                style="background: var(--debugbar-light); padding: 8px 12px; border-radius: 6px; font-family: monospace; font-size: 12px;">
                                {{ $extension }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (isset($translations) && isset($translations['files']) && count($translations['files']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Translation Files
                    ({{ $translations['loaded_files_count'] ?? 0 }})</h4>
                <div class="debugbar-collapsible-content">
                    <div class="debugbar-grid" style="margin-top: 16px;">
                        <div class="debugbar-metric">
                            <span class="debugbar-metric-label">Total Files Loaded</span>
                            <span class="debugbar-metric-value">{{ $translations['loaded_files_count'] ?? 0 }}</span>
                        </div>
                        <div class="debugbar-metric">
                            <span class="debugbar-metric-label">Total Load Time</span>
                            <span class="debugbar-metric-value">{{ $translations['total_load_time'] ?? 0 }}ms</span>
                        </div>
                        <div class="debugbar-metric">
                            <span class="debugbar-metric-label">Average Load Time</span>
                            <span class="debugbar-metric-value">{{ $translations['average_load_time'] ?? 0 }}ms</span>
                        </div>
                    </div>

                    <table class="debugbar-table" style="margin-top: 16px;">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Load Time</th>
                                <th>Memory Usage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($translations['files'] as $file)
                                <tr>
                                    <td style="font-family: monospace; font-size: 12px;">{{ $file['file'] }}
                                    </td>
                                    <td>{{ round($file['load_time'], 2) }}ms</td>
                                    <td>{{ isset($file['memory_delta']) ? __formatBytes($file['memory_delta']) : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
