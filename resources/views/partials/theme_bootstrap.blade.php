<meta name="theme-update-url" content="{{ route('theme.update') }}">
<script>
    (function () {
        try {
            var storedTheme = window.localStorage.getItem('studentedge-theme');
            var serverTheme = @json(session('theme', 'light'));
            var theme = storedTheme === 'dark' || storedTheme === 'light' ? storedTheme : serverTheme;
            var storedGlass = Number(window.localStorage.getItem('studentedge-glass-transparency'));
            var serverGlass = Number(@json(session('glass_transparency', 40)));
            var glass = Number.isFinite(storedGlass) && storedGlass >= 10 && storedGlass <= 65 ? storedGlass : serverGlass;
            document.documentElement.dataset.theme = theme;
            document.documentElement.style.colorScheme = theme;
            document.documentElement.style.setProperty('--glass-opacity', ((100 - glass) / 100).toFixed(2));
            document.documentElement.dataset.glassTransparency = String(glass);
        } catch (error) {
            document.documentElement.dataset.theme = @json(session('theme', 'light'));
            document.documentElement.style.setProperty('--glass-opacity', ((100 - Number(@json(session('glass_transparency', 40)))) / 100).toFixed(2));
        }
    })();
</script>
