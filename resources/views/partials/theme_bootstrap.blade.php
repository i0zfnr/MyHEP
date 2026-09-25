@php
    $themeAuthUser = session('auth_user', []);
    $themeRole = $themeAuthUser['role'] ?? null;
    $themeAdminRole = $themeAuthUser['admin_role'] ?? null;
    $themeLiquidDesignEnabled = $themeRole === 'student'
        || ($themeRole === 'admin' && app(\App\Support\SystemFeatures::class)->adminLiquidDesignEnabled(
            $themeAdminRole,
            (bool) session('liquid_design_enabled', false)
        ));
@endphp
<meta name="theme-update-url" content="{{ route('theme.update') }}">
<script>
    (function () {
        try {
            var liquidDesignEnabled = @json($themeLiquidDesignEnabled);
            var storedTheme = window.localStorage.getItem('myhep-theme');
            var serverTheme = @json(session('theme', 'light'));
            var theme = storedTheme === 'dark' || storedTheme === 'light' ? storedTheme : serverTheme;
            var storedAccent = window.localStorage.getItem('myhep-accent-theme');
            var serverAccent = @json(session('accent_theme', 'gold'));
            var accent = ['gold', 'candy_blue', 'lavender', 'orchid', 'violet', 'pink', 'red'].includes(serverAccent) ? serverAccent : storedAccent;
            var storedStudentGlass = window.localStorage.getItem('studentedge-glass-transparency');
            var storedGlassValue = storedStudentGlass !== null ? storedStudentGlass : window.localStorage.getItem('myhep-glass-transparency');
            var storedGlass = storedGlassValue === null || storedGlassValue === '' ? Number.NaN : Number(storedGlassValue);
            var serverGlass = Number(@json(session('glass_transparency', 40)));
            var glass = Number.isFinite(storedGlass) && storedGlass >= 0 && storedGlass <= 100 ? storedGlass : serverGlass;
            var glassRatio = Math.min(100, Math.max(0, glass)) / 100;
            document.documentElement.dataset.theme = theme;
            document.documentElement.dataset.accentTheme = accent;
            document.documentElement.dataset.liquidDesign = liquidDesignEnabled ? 'on' : 'off';
            document.documentElement.style.colorScheme = theme;
            document.documentElement.style.setProperty('--glass-user-transparency', liquidDesignEnabled ? glassRatio.toFixed(2) : '0');
            document.documentElement.style.setProperty('--glass-opacity', liquidDesignEnabled ? (0.86 - (glassRatio * 0.34)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--student-nav-material-alpha', liquidDesignEnabled ? (0.86 - (glassRatio * 0.34)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--student-nav-active-alpha', liquidDesignEnabled ? (0.80 - (glassRatio * 0.18)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--student-nav-reflection-alpha', liquidDesignEnabled ? (0.34 - (glassRatio * 0.16)).toFixed(2) : '0');
            document.documentElement.style.setProperty('--student-nav-active-reflection-alpha', liquidDesignEnabled ? (0.46 - (glassRatio * 0.24)).toFixed(2) : '0');
            document.documentElement.style.setProperty('--student-nav-blur', liquidDesignEnabled ? (18 + (glassRatio * 8)) + 'px' : '0px');
            document.documentElement.style.setProperty('--student-nav-active-blur', liquidDesignEnabled ? (10 + (glassRatio * 4)) + 'px' : '0px');
            document.documentElement.style.setProperty('--student-nav-saturation', liquidDesignEnabled ? (132 + (glassRatio * 16)) + '%' : '100%');
            document.documentElement.dataset.glassTransparency = String(glass);
            document.documentElement.dataset.glassHigh = liquidDesignEnabled && glass >= 70 ? 'true' : 'false';
        } catch (error) {
            document.documentElement.dataset.theme = @json(session('theme', 'light'));
            document.documentElement.dataset.accentTheme = @json(session('accent_theme', 'gold'));
            document.documentElement.dataset.liquidDesign = @json($themeLiquidDesignEnabled) ? 'on' : 'off';
            document.documentElement.style.setProperty('--glass-opacity', @json($themeLiquidDesignEnabled) ? '.72' : '1');
            document.documentElement.style.setProperty('--student-nav-material-alpha', @json($themeLiquidDesignEnabled) ? '.72' : '1');
            document.documentElement.style.setProperty('--student-nav-active-alpha', @json($themeLiquidDesignEnabled) ? '.74' : '1');
            document.documentElement.style.setProperty('--student-nav-reflection-alpha', @json($themeLiquidDesignEnabled) ? '.26' : '0');
            document.documentElement.style.setProperty('--student-nav-active-reflection-alpha', @json($themeLiquidDesignEnabled) ? '.34' : '0');
            document.documentElement.style.setProperty('--student-nav-blur', @json($themeLiquidDesignEnabled) ? '22px' : '0px');
            document.documentElement.style.setProperty('--student-nav-active-blur', @json($themeLiquidDesignEnabled) ? '12px' : '0px');
            document.documentElement.style.setProperty('--student-nav-saturation', @json($themeLiquidDesignEnabled) ? '142%' : '100%');
        }
    })();
</script>
