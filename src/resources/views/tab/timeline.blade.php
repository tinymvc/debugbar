<div id="timeline" class="debugbar-panel">
    <div class="debugbar-panel-content">
        <div class="debugbar-section">
            <h3 class="debugbar-section-title">Execution Timeline</h3>

            @if (isset($timeline) && count($timeline) > 0)
                <div class="debugbar-timeline">
                    @php
                        $maxTime = 0;
                        foreach ($timeline as $event) {
                            $endTime = $event['start'] + $event['duration'];
                            if ($endTime > $maxTime) {
                                $maxTime = $endTime;
                            }
                        }
                        $maxTime = max($maxTime, 1); // Prevent division by zero
                    @endphp

                    @foreach ($timeline as $event)
                        <div class="debugbar-timeline-bar">
                            <div class="debugbar-timeline-segment"
                                style="left: {{ ($event['start'] / $maxTime) * 100 }}%; 
                                               width: {{ max(($event['duration'] / $maxTime) * 100, 0.5) }}%; 
                                               background-color: {{ $event['color'] ?? '#3498db' }};"
                                title="{{ $event['name'] }} ({{ $event['duration'] }}ms)">
                                {{ $event['name'] }}
                            </div>
                        </div>
                    @endforeach

                    <div
                        style="margin-top: 20px; padding: 16px; background: var(--debugbar-light); border-radius: 8px;">
                        <strong>Timeline Legend:</strong>
                        <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-top: 8px;">
                            @php
                                $groups = array_unique(array_column($timeline, 'group'));
                            @endphp
                            @foreach ($groups as $group)
                                @php
                                    $groupColor = '';
                                    foreach ($timeline as $event) {
                                        if ($event['group'] === $group) {
                                            $groupColor = $event['color'] ?? '#3498db';
                                            break;
                                        }
                                    }
                                @endphp
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div
                                        style="width: 16px; height: 16px; background: {{ $groupColor }}; border-radius: 3px;">
                                    </div>
                                    <span>{{ $group }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <h4 class="debugbar-collapsible" onclick="toggleCollapsible(this)">Timeline Events</h4>
                <div class="debugbar-collapsible-content">
                    <table class="debugbar-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Group</th>
                                <th>Start (ms)</th>
                                <th>Duration (ms)</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($timeline as $event)
                                <tr>
                                    <td><strong>{{ $event['name'] }}</strong></td>
                                    <td>
                                        <span
                                            style="background: {{ $event['color'] ?? '#3498db' }}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px;">
                                            {{ $event['group'] }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($event['start'], 2) }}</td>
                                    <td>{{ number_format($event['duration'], 2) }}</td>
                                    <td>
                                        @if (isset($event['details']))
                                            <code>{{ $event['details'] }}</code>
                                        @else
                                            <em>-</em>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: var(--debugbar-text-muted);">
                    <div style="font-size: 48px; margin-bottom: 16px;">📊</div>
                    <p>No timeline events were recorded.</p>
                </div>
            @endif
        </div>
    </div>
</div>
