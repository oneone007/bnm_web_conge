// Theme switching functionality
document.addEventListener('DOMContentLoaded', function() {
    // Function to apply theme
    function applyTheme(isDark) {
        document.body.classList.toggle('dark-mode', isDark);
        const charts = window.charts || []; // Access any Chart.js instances if they exist
        charts.forEach(chart => {
            updateChartTheme(chart, isDark);
        });
    }

    // Function to update chart theme
    function updateChartTheme(chart, isDark) {
        if (!chart) return;
        
        const textColor = isDark ? '#F3F4F6' : '#1F2937';
        const gridColor = isDark ? '#374151' : '#E5E7EB';

        chart.options.scales.x.grid.color = gridColor;
        chart.options.scales.y.grid.color = gridColor;
        chart.options.scales.x.ticks.color = textColor;
        chart.options.scales.y.ticks.color = textColor;
        chart.options.plugins.legend.labels.color = textColor;
        chart.update();
    }

    // Listen for theme changes from localStorage
    window.addEventListener('storage', (e) => {
        if (e.key === 'theme') {
            const isDark = e.newValue === 'dark';
            applyTheme(isDark);
        }
    });

    // Listen for custom theme change events
    window.addEventListener('themeChanged', (e) => {
        const isDark = e.detail.isDark;
        applyTheme(isDark);
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    // Apply initial theme
    const isDark = localStorage.getItem('theme') === 'dark';
    applyTheme(isDark);

    // Check for theme changes every second (as fallback)
    setInterval(() => {
        const currentTheme = localStorage.getItem('theme');
        const shouldBeDark = currentTheme === 'dark';
        const isDark = document.body.classList.contains('dark-mode');
        
        if (shouldBeDark !== isDark) {
            applyTheme(shouldBeDark);
        }
    }, 1000);
});

  
        // Check and apply theme on page load
        const isDarkMode = localStorage.getItem('theme') === 'dark';
        if (isDarkMode) {
            document.documentElement.classList.add('dark');
        }

        // Listen for theme changes from sidebar
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme') {
                document.documentElement.classList.toggle('dark', e.newValue === 'dark');
            }
        });