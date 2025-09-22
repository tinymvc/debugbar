<script>
    let debugBarMinimized = true;

    function toggleDebugBar() {
        const debugBar = document.getElementById('tinymvc-debugbar');
        const toggleIcon = document.getElementById('debugbar-toggle-icon');

        debugBarMinimized = !debugBarMinimized;

        if (debugBarMinimized) {
            debugBar.classList.add('minimized');
            toggleIcon.textContent = '▲';
        } else {
            debugBar.classList.remove('minimized');
            toggleIcon.textContent = '▼';
        }
    }

    function showPanel(panelId) {
        // Hide all panels
        document.querySelectorAll('.debugbar-panel').forEach(panel => {
            panel.classList.remove('active');
        });

        // Remove active class from all tabs
        document.querySelectorAll('.debugbar-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected panel
        const panel = document.getElementById(panelId);
        if (panel) {
            panel.classList.add('active');
        }

        // Add active class to clicked tab
        event.target.classList.add('active');
    }

    function toggleCollapsible(element) {
        element.classList.toggle('collapsed');
    }

    // Prevent event propagation
    document.getElementById('tinymvc-debugbar').addEventListener('click', function(e) {
        e.stopPropagation();
    });
</script>
