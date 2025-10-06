<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snapshots - Debugbar List</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            background: #f5f7fa;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 400;
        }

        .snapshot-count {
            color: #7f8c8d;
            font-size: 0.95rem;
            margin-top: 0.3rem;
        }

        .snapshots-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .snapshot-item {
            display: flex;
            align-items: center;
            padding: 1.25rem 2rem;
            border-bottom: 1px solid #ecf0f1;
            transition: background-color 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .snapshot-item:last-child {
            border-bottom: none;
        }

        .snapshot-item:hover {
            background-color: #f8f9fa;
        }

        .snapshot-method {
            min-width: 60px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            text-align: center;
            margin-right: 1rem;
        }

        .method-get {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .method-post {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .method-put {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .method-delete {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .snapshot-main {
            flex: 1;
            min-width: 0;
        }

        .snapshot-url {
            font-weight: 500;
            color: #2c3e50;
            font-size: 1rem;
            margin-bottom: 0.4rem;
            font-family: 'Courier New', monospace;
        }

        .snapshot-time {
            color: #7f8c8d;
            font-size: 0.85rem;
        }

        .snapshot-stats {
            display: flex;
            gap: 2rem;
            align-items: center;
            margin-right: 1.5rem;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 80px;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.2rem;
        }

        .stat-value {
            font-weight: 600;
            font-size: 0.95rem;
            color: #2c3e50;
        }

        .stat-execution {
            color: #9b59b6;
        }

        .stat-memory {
            color: #3498db;
        }

        .stat-queries {
            color: #e74c3c;
        }

        .snapshot-actions {
            display: flex;
            gap: 0.5rem;
        }

        .view-button,
        .refresh-button {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .refresh-button {
            background: #43a047;
            padding: 0.8rem 1.6 rem;
        }

        .view-button:hover {
            background: #5568d3;
        }

        .refresh-button:hover {
            background: #388e3c;
        }

        .no-snapshots {
            background: white;
            border-radius: 8px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .no-snapshots-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .no-snapshots-text {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        @media (max-width: 1024px) {
            .snapshot-stats {
                gap: 1rem;
            }

            .stat-item {
                min-width: 60px;
            }
        }

        @media (max-width: 768px) {
            .snapshot-item {
                flex-wrap: wrap;
                padding: 1rem;
            }

            .snapshot-stats {
                width: 100%;
                margin-top: 1rem;
                margin-right: 0;
                justify-content: space-around;
            }

            .snapshot-actions {
                width: 100%;
                margin-top: 1rem;
            }

            .view-button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;color: #2c3e50;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-search-icon lucide-search">
                        <path d="m21 21-4.34-4.34" />
                        <circle cx="11" cy="11" r="8" />
                    </svg>
                    <h1>Debug Snapshots</h1>
                </div>
                @if (isset($snapshots) && count($snapshots) > 0)
                    <div class="snapshot-count">{{ count($snapshots) }} snapshot(s) found</div>
                @endif
            </div>
            <a href="/debugbar" class="refresh-button">Refresh</a>
        </div>

        @if (isset($snapshots) && count($snapshots) > 0)
            <div class="snapshots-list">
                @foreach ($snapshots as $snapshot)
                    <a href="/debugbar/{{ $snapshot['file'] }}" class="snapshot-item">
                        <div class="snapshot-method method-{{ strtolower($snapshot['method']) }}">
                            {{ $snapshot['method'] }}
                        </div>

                        <div class="snapshot-main">
                            <div class="snapshot-url">{{ $snapshot['url'] }}</div>
                            <div class="snapshot-time">
                                {{ carbon($snapshot['time'])->format('d M, Y g:i:s A') }} •
                                <small>({{ carbon($snapshot['time'])->diffForHumansShort() }})</small>
                            </div>
                        </div>

                        <div class="snapshot-stats">
                            <div class="stat-item">
                                <div class="stat-label">Time</div>
                                <div class="stat-value stat-execution">
                                    {{ number_format($snapshot['summary']['execution_time'], 2) }}ms</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Memory</div>
                                <div class="stat-value stat-memory">{{ $snapshot['summary']['memory_used'] }}</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Queries</div>
                                <div class="stat-value stat-queries">{{ $snapshot['summary']['database_query_count'] }}
                                </div>
                            </div>
                        </div>

                        <div class="snapshot-actions">
                            <span class="view-button">View Details</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="no-snapshots">
                <div class="no-snapshots-icon">📭</div>
                <div class="no-snapshots-text">No snapshots found</div>
            </div>
        @endif
    </div>
</body>

</html>
