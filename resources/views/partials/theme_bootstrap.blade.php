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
            var storedSolid = window.localStorage.getItem('studentedge-glass-solid');
            var solidGlass = storedSolid === '1' || (storedSolid !== '0' && @json((bool) session('glass_solid', false)));
            var glassActive = liquidDesignEnabled && !solidGlass;
            document.documentElement.dataset.theme = theme;
            document.documentElement.dataset.accentTheme = accent;
            document.documentElement.dataset.liquidDesign = liquidDesignEnabled ? 'on' : 'off';
            document.documentElement.dataset.glassMaterial = solidGlass ? 'solid' : 'glass';
            document.documentElement.style.colorScheme = theme;
            document.documentElement.style.setProperty('--glass-user-transparency', glassActive ? glassRatio.toFixed(2) : '0');
            document.documentElement.style.setProperty('--glass-opacity', glassActive ? (0.86 - (glassRatio * 0.34)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-nav-opacity', glassActive ? (0.88 - (glassRatio * 0.22)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-nav-opacity-dark', glassActive ? (0.92 - (glassRatio * 0.18)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-header-opacity', glassActive ? (0.88 - (glassRatio * 0.22)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-header-opacity-dark', glassActive ? (0.90 - (glassRatio * 0.20)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-popup-opacity', glassActive ? (0.96 - (glassRatio * 0.22)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-popup-opacity-dark', glassActive ? (0.97 - (glassRatio * 0.17)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-drawer-opacity', glassActive ? (0.80 - (glassRatio * 0.34)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-drawer-opacity-dark', glassActive ? (0.86 - (glassRatio * 0.25)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-user-blur', glassActive ? (12 + (glassRatio * 10)) + 'px' : '0px');
            document.documentElement.style.setProperty('--student-nav-material-alpha', glassActive ? (0.98 - (glassRatio * 0.10)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--student-nav-active-alpha', glassActive ? (0.80 - (glassRatio * 0.18)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--student-nav-reflection-alpha', glassActive ? (0.34 - (glassRatio * 0.16)).toFixed(2) : '0');
            document.documentElement.style.setProperty('--student-nav-active-reflection-alpha', glassActive ? (0.46 - (glassRatio * 0.24)).toFixed(2) : '0');
            document.documentElement.style.setProperty('--student-nav-blur', glassActive ? (18 + (glassRatio * 8)) + 'px' : '0px');
            document.documentElement.style.setProperty('--student-nav-active-blur', glassActive ? (10 + (glassRatio * 4)) + 'px' : '0px');
            document.documentElement.style.setProperty('--student-nav-saturation', glassActive ? (132 + (glassRatio * 16)) + '%' : '100%');
            document.documentElement.dataset.glassTransparency = String(glass);
            document.documentElement.dataset.glassHigh = glassActive && glass >= 70 ? 'true' : 'false';
        } catch (error) {
            document.documentElement.dataset.theme = @json(session('theme', 'light'));
            document.documentElement.dataset.accentTheme = @json(session('accent_theme', 'gold'));
            document.documentElement.dataset.liquidDesign = @json($themeLiquidDesignEnabled) ? 'on' : 'off';
            document.documentElement.dataset.glassMaterial = @json((bool) session('glass_solid', false)) ? 'solid' : 'glass';
            document.documentElement.style.setProperty('--glass-opacity', @json($themeLiquidDesignEnabled) ? '.72' : '1');
            document.documentElement.style.setProperty('--myhep-nav-opacity', @json($themeLiquidDesignEnabled) ? '.79' : '1');
            document.documentElement.style.setProperty('--myhep-nav-opacity-dark', @json($themeLiquidDesignEnabled) ? '.85' : '1');
            document.documentElement.style.setProperty('--myhep-header-opacity', @json($themeLiquidDesignEnabled) ? '.79' : '1');
            document.documentElement.style.setProperty('--myhep-header-opacity-dark', @json($themeLiquidDesignEnabled) ? '.82' : '1');
            document.documentElement.style.setProperty('--myhep-popup-opacity', @json($themeLiquidDesignEnabled) ? '.87' : '1');
            document.documentElement.style.setProperty('--myhep-popup-opacity-dark', @json($themeLiquidDesignEnabled) ? '.90' : '1');
            document.documentElement.style.setProperty('--myhep-drawer-opacity', @json($themeLiquidDesignEnabled) ? '.66' : '1');
            document.documentElement.style.setProperty('--myhep-drawer-opacity-dark', @json($themeLiquidDesignEnabled) ? '.76' : '1');
            document.documentElement.style.setProperty('--myhep-user-blur', @json($themeLiquidDesignEnabled) ? '16px' : '0px');
            document.documentElement.style.setProperty('--student-nav-material-alpha', @json($themeLiquidDesignEnabled) ? '.94' : '1');
            document.documentElement.style.setProperty('--student-nav-active-alpha', @json($themeLiquidDesignEnabled) ? '.74' : '1');
            document.documentElement.style.setProperty('--student-nav-reflection-alpha', @json($themeLiquidDesignEnabled) ? '.26' : '0');
            document.documentElement.style.setProperty('--student-nav-active-reflection-alpha', @json($themeLiquidDesignEnabled) ? '.34' : '0');
            document.documentElement.style.setProperty('--student-nav-blur', @json($themeLiquidDesignEnabled) ? '22px' : '0px');
            document.documentElement.style.setProperty('--student-nav-active-blur', @json($themeLiquidDesignEnabled) ? '12px' : '0px');
            document.documentElement.style.setProperty('--student-nav-saturation', @json($themeLiquidDesignEnabled) ? '142%' : '100%');
        }
    })();
</script>
