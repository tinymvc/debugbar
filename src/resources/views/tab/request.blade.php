<div id="request" class="debugbar-panel">
    <div class="debugbar-panel-content">
        <div class="debugbar-section">
            <h3 class="debugbar-section-title">Request Information</h3>

            <div class="debugbar-grid">
                <div class="debugbar-card">
                    <div class="debugbar-card-title">Method</div>
                    <div class="debugbar-card-value">{{ $request['method'] ?? 'N/A' }}</div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">URL</div>
                    <div class="debugbar-card-value" style="font-size: 16px;">{{ $request['url'] ?? 'N/A' }}
                    </div>
                </div>

                <div class="debugbar-card">
                    <div class="debugbar-card-title">Client IP</div>
                    <div class="debugbar-card-value" style="font-size: 18px;">{{ $request['ip'] ?? 'N/A' }}
                    </div>
                </div>
            </div>

            @if (isset($request['route_info']) && count($request['route_info']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Route Information</h4>
                <div class="debugbar-collapsible-content">
                    @php
                        echo '<div class="debugbar-code">';
                        echo json_encode(
                            $request['route_info'],
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                        );
                        echo '</div>';
                    @endphp
                </div>
            @endif

            @if (isset($request['middlewares']) && count($request['middlewares']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Middlewares</h4>
                <div class="debugbar-collapsible-content">
                    @php
                        echo '<div class="debugbar-code">';
                        echo json_encode($request['middlewares'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        echo '</div>';
                    @endphp
                </div>
            @endif

            @if (isset($request['headers']) && count($request['headers']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Headers</h4>
                <div class="debugbar-collapsible-content">
                    <table class="debugbar-table">
                        <thead>
                            <tr>
                                <th>Header</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($request['headers'] as $name => $value)
                                <tr>
                                    <td width="30%"><strong>{{ $name }}</strong></td>
                                    <td width="70%"><code>{{ $value }}</code></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (isset($request['get']) && count($request['get']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">GET Parameters</h4>
                <div class="debugbar-collapsible-content">
                    @php
                        echo '<div class="debugbar-code">';
                        echo json_encode($request['get'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        echo '</div>';
                    @endphp
                </div>
            @endif

            @if (isset($request['post']) && count($request['post']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">POST Data</h4>
                <div class="debugbar-collapsible-content">
                    @php
                        echo '<div class="debugbar-code">';
                        echo json_encode($request['post'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        echo '</div>';
                    @endphp
                </div>
            @endif

            @if (isset($request['cookies']) && count($request['cookies']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Cookies</h4>
                <div class="debugbar-collapsible-content">
                    @php
                        echo '<div class="debugbar-code">';
                        echo json_encode($request['cookies'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        echo '</div>';
                    @endphp
                </div>
            @endif

            @if (isset($request['session']) && count($request['session']) > 0)
                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Session Data</h4>
                <div class="debugbar-collapsible-content">
                    @php
                        echo '<div class="debugbar-code">';
                        echo json_encode($request['session'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        echo '</div>';
                    @endphp
                </div>
            @endif
        </div>
    </div>
</div>
