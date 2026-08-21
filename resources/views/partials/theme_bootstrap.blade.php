<meta name="theme-update-url" content="{{ route('theme.update') }}">
<script>
    (function () {
        try {
            var storedTheme = window.localStorage.getItem('myhep-theme');
            var serverTheme = @json(session('theme', 'light'));
            var theme = storedTheme === 'dark' || storedTheme === 'light' ? storedTheme : serverTheme;
            var storedAccent = window.localStorage.getItem('myhep-accent-theme');
            var serverAccent = @json(session('accent_theme', 'gold'));
            var accent = ['gold', 'candy_blue', 'lavender', 'orchid', 'violet'].includes(serverAccent) ? serverAccent : storedAccent;
            var storedGlass = Number(window.localStorage.getItem('myhep-glass-transparency'));
            var serverGlass = Number(@json(session('glass_transparency', 40)));
            var glass = Number.isFinite(storedGlass) && storedGlass >= 10 && storedGlass <= 65 ? storedGlass : serverGlass;
            document.documentElement.dataset.theme = theme;
            document.documentElement.dataset.accentTheme = accent;
            document.documentElement.style.colorScheme = theme;
            document.documentElement.style.setProperty('--glass-opacity', ((100 - glass) / 100).toFixed(2));
            document.documentElement.dataset.glassTransparency = String(glass);
        } catch (error) {
            document.documentElement.dataset.theme = @json(session('theme', 'light'));
            document.documentElement.dataset.accentTheme = @json(session('accent_theme', 'gold'));
            document.documentElement.style.setProperty('--glass-opacity', ((100 - Number(@json(session('glass_transparency', 40)))) / 100).toFixed(2));
        }
    })();
</script>
