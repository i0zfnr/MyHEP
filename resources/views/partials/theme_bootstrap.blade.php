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
            var storedStudentGlass = window.localStorage.getItem('studentedge-glass-transparency');
            var storedGlass = Number(storedStudentGlass !== null ? storedStudentGlass : window.localStorage.getItem('myhep-glass-transparency'));
            var serverGlass = Number(@json(session('glass_transparency', 40)));
            var glass = Number.isFinite(storedGlass) && storedGlass >= 0 && storedGlass <= 100 ? storedGlass : serverGlass;
            var glassRatio = Math.min(100, Math.max(0, glass)) / 100;
            document.documentElement.dataset.theme = theme;
            document.documentElement.dataset.accentTheme = accent;
            document.documentElement.style.colorScheme = theme;
            document.documentElement.style.setProperty('--glass-user-transparency', glassRatio.toFixed(2));
            document.documentElement.style.setProperty('--student-nav-material-alpha', (0.86 - (glassRatio * 0.34)).toFixed(2));
            document.documentElement.style.setProperty('--student-nav-active-alpha', (0.90 - (glassRatio * 0.22)).toFixed(2));
            document.documentElement.style.setProperty('--student-nav-reflection-alpha', (0.34 - (glassRatio * 0.16)).toFixed(2));
            document.documentElement.style.setProperty('--student-nav-active-reflection-alpha', (0.46 - (glassRatio * 0.24)).toFixed(2));
            document.documentElement.style.setProperty('--student-nav-blur', (18 + (glassRatio * 8)) + 'px');
            document.documentElement.style.setProperty('--student-nav-active-blur', (10 + (glassRatio * 4)) + 'px');
            document.documentElement.style.setProperty('--student-nav-saturation', (132 + (glassRatio * 16)) + '%');
            document.documentElement.dataset.glassTransparency = String(glass);
        } catch (error) {
            document.documentElement.dataset.theme = @json(session('theme', 'light'));
            document.documentElement.dataset.accentTheme = @json(session('accent_theme', 'gold'));
            document.documentElement.style.setProperty('--student-nav-material-alpha', '.72');
            document.documentElement.style.setProperty('--student-nav-active-alpha', '.81');
            document.documentElement.style.setProperty('--student-nav-reflection-alpha', '.26');
            document.documentElement.style.setProperty('--student-nav-active-reflection-alpha', '.34');
            document.documentElement.style.setProperty('--student-nav-blur', '22px');
            document.documentElement.style.setProperty('--student-nav-active-blur', '12px');
            document.documentElement.style.setProperty('--student-nav-saturation', '142%');
        }
    })();
</script>
