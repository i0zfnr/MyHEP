<meta name="theme-update-url" content="{{ route('theme.update') }}">
<script>
    (function () {
        try {
            var storedTheme = window.localStorage.getItem('studentedge-theme');
            var serverTheme = @json(session('theme', 'light'));
            var theme = storedTheme === 'dark' || storedTheme === 'light' ? storedTheme : serverTheme;
            document.documentElement.dataset.theme = theme;
            document.documentElement.style.colorScheme = theme;
        } catch (error) {
            document.documentElement.dataset.theme = @json(session('theme', 'light'));
        }
    })();
</script>
