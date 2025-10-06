<style>
    :root {
        --debugbar-primary: #1e293b;
        --debugbar-secondary: #334155;
        --debugbar-tertiary: #475569;
        --debugbar-accent: #3b82f6;
        --debugbar-success: #10b981;
        --debugbar-warning: #f59e0b;
        --debugbar-danger: #ef4444;
        --debugbar-light: #f1f5f9;
        --debugbar-text: #1e293b;
        --debugbar-text-light: #64748b;
        --debugbar-text-muted: #94a3b8;
        --debugbar-border: #e2e8f0;
        --debugbar-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.05);
        --debugbar-shadow-lg: 0 25px 50px -12px rgb(0 0 0 / 0.25);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    #tinymvc-debugbar * {
        box-sizing: border-box;
    }

    #tinymvc-debugbar {
        position: fixed;
        bottom: 0px;
        left: 0;
        right: 0;
        z-index: 999999;
        font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 13px;
        background: var(--debugbar-primary);
        color: white;
        box-shadow: var(--debugbar-shadow-lg);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
    }

    #tinymvc-debugbar.is_full {
        position: relative;
    }

    #tinymvc-debugbar.minimized {
        transform: translateY(calc(100% - 52px));
    }

    .debugbar-header {
        background: linear-gradient(135deg, var(--debugbar-secondary) 0%, var(--debugbar-tertiary) 100%);
        padding: 8px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        border-bottom: 1px solid #475569;
        backdrop-filter: blur(8px);
    }

    #tinymvc-debugbar.is_full .debugbar-header {
        cursor: default;
    }

    .debugbar-title {
        display: flex;
        align-items: center;
        font-weight: 700;
        gap: 12px;
        font-size: 14px;
        letter-spacing: -0.02em;
    }

    .debugbar-logo {
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, var(--debugbar-accent) 0%, #6366f1 100%);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    .debugbar-summary {
        display: flex;
        gap: 24px;
        font-size: 12px;
        color: #cbd5e1;
        font-weight: 500;
    }

    .debugbar-summary-item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        transition: background 0.2s;
    }

    .debugbar-summary-item:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .debugbar-toggle {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 8px;
        font-size: 16px;
        border-radius: 4px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .debugbar-toggle:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: scale(1.05);
    }

    .debugbar-content {
        max-height: 65vh;
        overflow: hidden;
        background: white;
        color: var(--debugbar-text);
        display: flex;
        flex-direction: column;
    }

    #tinymvc-debugbar.is_full .debugbar-content {
        height: 100%;
        max-height: calc(100% - 45px);
    }

    .debugbar-tabs {
        display: flex;
        background: var(--debugbar-light);
        border-bottom: 1px solid var(--debugbar-border);
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        position: relative;
        z-index: 10;
        min-height: 52px;
    }

    .debugbar-tabs::-webkit-scrollbar {
        display: none;
    }

    .debugbar-tab {
        padding: 14px 20px;
        cursor: pointer;
        white-space: nowrap;
        border-bottom: 3px solid transparent;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        color: var(--debugbar-text-light);
        position: relative;
        min-width: 120px;
        height: 52px;
        box-sizing: border-box;
        z-index: 5;
        flex-shrink: 0;
    }

    .debugbar-tab:hover {
        background: #e2e8f0;
        color: var(--debugbar-text);
        z-index: 6;
    }

    .debugbar-tab.active {
        background: white;
        border-bottom-color: var(--debugbar-accent);
        color: var(--debugbar-accent);
        font-weight: 600;
        z-index: 15;
        height: 52px;
    }

    .debugbar-badge {
        background: var(--debugbar-accent);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        min-width: 20px;
        text-align: center;
        letter-spacing: 0.025em;
    }

    .debugbar-tab.danger .debugbar-badge {
        background: var(--debugbar-danger);
    }

    .debugbar-tab.warning .debugbar-badge {
        background: var(--debugbar-warning);
    }

    .debugbar-tab.success .debugbar-badge {
        background: var(--debugbar-success);
    }

    .debugbar-panel {
        display: none;
        flex: 1;
        overflow-y: auto;
        background: white;
        scrollbar-width: thin;
        scrollbar-color: var(--debugbar-border) transparent;
        position: relative;
        z-index: 1;
    }

    .debugbar-panel::-webkit-scrollbar {
        width: 8px;
    }

    .debugbar-panel::-webkit-scrollbar-track {
        background: transparent;
    }

    .debugbar-panel::-webkit-scrollbar-thumb {
        background: var(--debugbar-border);
        border-radius: 4px;
    }

    .debugbar-panel::-webkit-scrollbar-thumb:hover {
        background: var(--debugbar-text-muted);
    }

    .debugbar-panel.active {
        display: block;
    }

    .debugbar-panel-content {
        padding: 24px;
    }

    .debugbar-section {
        margin-bottom: 32px;
    }

    .debugbar-section-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--debugbar-text);
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--debugbar-light);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .debugbar-section-title::before {
        content: '';
        width: 4px;
        height: 20px;
        background: linear-gradient(135deg, var(--debugbar-accent), #6366f1);
        border-radius: 2px;
    }

    .debugbar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .debugbar-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid var(--debugbar-border);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }

    .debugbar-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--debugbar-accent), #6366f1);
    }

    .debugbar-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--debugbar-shadow);
    }

    .debugbar-card-title {
        font-weight: 600;
        color: var(--debugbar-text-light);
        margin-bottom: 8px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .debugbar-card-value {
        font-size: 24px;
        font-weight: 800;
        color: var(--debugbar-accent);
        font-family: ui-monospace, SFMono-Regular, "SF Mono", monospace;
    }

    .debugbar-card-subtitle {
        font-size: 11px;
        color: var(--debugbar-text-muted);
        margin-top: 4px;
    }

    .debugbar-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
    }

    .debugbar-table th {
        background: var(--debugbar-primary);
        color: white;
        padding: 16px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .debugbar-table td {
        padding: 16px;
        border-bottom: 1px solid var(--debugbar-border);
        vertical-align: top;
    }

    .debugbar-table tbody tr:hover {
        background: var(--debugbar-light);
    }

    .debugbar-table tbody tr:last-child td {
        border-bottom: none;
    }

    .debugbar-code {
        background: #0f172a;
        color: #e2e8f0;
        padding: 16px;
        border-radius: 8px;
        font-family: ui-monospace, SFMono-Regular, "SF Mono", monospace;
        font-size: 12px;
        overflow-x: auto;
        margin: 8px 0;
        white-space: pre-wrap;
        word-break: break-all;
        line-height: 1.5;
        border-left: 4px solid var(--debugbar-accent);
    }

    .debugbar-code p:not(:last-child) {
        margin-bottom: 10px;
    }

    .debugbar-timeline {
        position: relative;
        background: var(--debugbar-light);
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
        min-height: 120px;
    }

    .debugbar-timeline-bar {
        position: relative;
        height: 32px;
        background: #e2e8f0;
        border-radius: 6px;
        margin: 8px 0;
        overflow: hidden;
    }

    .debugbar-timeline-segment {
        position: absolute;
        height: 100%;
        border-radius: 6px;
        display: flex;
        align-items: center;
        padding: 0 12px;
        color: white;
        font-size: 11px;
        font-weight: 600;
        text-shadow: 0 1px 2px rgb(0 0 0 / 0.3);
        cursor: pointer;
        transition: all 0.2s;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .debugbar-timeline-segment:hover {
        opacity: 0.85;
        transform: scale(1.02);
    }

    .debugbar-log-entry {
        padding: 12px 16px;
        border-left: 4px solid var(--debugbar-accent);
        margin-bottom: 12px;
        background: var(--debugbar-light);
        border-radius: 0 8px 8px 0;
        transition: all 0.2s;
    }

    .debugbar-log-entry:hover {
        background: #e2e8f0;
    }

    .debugbar-log-entry.error {
        border-left-color: var(--debugbar-danger);
        background: #fef2f2;
    }

    .debugbar-log-entry.warning {
        border-left-color: var(--debugbar-warning);
        background: #fffbeb;
    }

    .debugbar-log-entry.success {
        border-left-color: var(--debugbar-success);
        background: #f0fdf4;
    }

    .debugbar-log-entry.info {
        border-left-color: var(--debugbar-accent);
    }

    .debugbar-log-message {
        font-weight: 600;
        margin-bottom: 4px;
    }

    .debugbar-log-meta {
        font-size: 11px;
        color: var(--debugbar-text-light);
        display: flex;
        gap: 16px;
        align-items: center;
        margin-bottom: 5px;
    }

    .debugbar-collapsible {
        cursor: pointer;
        user-select: none;
        padding: 6px 0;
        font-weight: 600;
        color: var(--debugbar-text);
        display: flex;
        align-items: center;
        transition: color 0.2s;
    }

    .debugbar-collapsible:hover {
        color: var(--debugbar-accent);
    }

    .debugbar-collapsible:before {
        content: '▼';
        margin-right: 12px;
        transition: transform 0.2s;
        color: var(--debugbar-accent);
    }

    .debugbar-collapsible.collapsed:before {
        transform: rotate(-90deg);
    }

    .debugbar-collapsible-content {
        margin-top: 10px;
        animation: slideDown 0.2s ease-out;
    }

    .debugbar-collapsible.collapsed+.debugbar-collapsible-content {
        display: none;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .debugbar-status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .status-success {
        background: var(--debugbar-success);
    }

    .status-warning {
        background: var(--debugbar-warning);
    }

    .status-danger {
        background: var(--debugbar-danger);
    }

    .status-info {
        background: var(--debugbar-accent);
    }

    .debugbar-metric {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: white;
        border-radius: 6px;
        margin-bottom: 8px;
        border: 1px solid var(--debugbar-border);
    }

    .debugbar-metric-label {
        font-weight: 500;
        color: var(--debugbar-text-light);
    }

    .debugbar-metric-value {
        font-weight: 700;
        font-family: ui-monospace, SFMono-Regular, "SF Mono", monospace;
        color: var(--debugbar-text);
    }

    @media (max-width: 768px) {
        .debugbar-summary {
            display: none;
        }

        .debugbar-grid {
            grid-template-columns: 1fr;
        }

        .debugbar-content {
            max-height: 60vh;
        }

        .debugbar-panel-content {
            padding: 16px;
        }

        .debugbar-tab {
            padding: 12px 16px;
            min-width: 100px;
        }
    }

    .highlight-sql {
        color: #3b82f6;
    }

    .highlight-number {
        color: #ef4444;
    }

    .highlight-string {
        color: #10b981;
    }

    .highlight-keyword {
        color: #8b5cf6;
        font-weight: 600;
    }

    .performance-good {
        color: var(--debugbar-success);
    }

    .performance-warning {
        color: var(--debugbar-warning);
    }

    .performance-danger {
        color: var(--debugbar-danger);
    }
</style>
