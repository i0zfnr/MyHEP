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
            var storedFrostValue = window.localStorage.getItem('studentedge-glass-frost');
            var storedLegacyGlass = window.localStorage.getItem('studentedge-glass-transparency');
            if (storedLegacyGlass === null) storedLegacyGlass = window.localStorage.getItem('myhep-glass-transparency');
            var storedGlass = storedFrostValue !== null && storedFrostValue !== ''
                ? Number(storedFrostValue)
                : storedLegacyGlass !== null && storedLegacyGlass !== ''
                    ? 100 - Number(storedLegacyGlass)
                    : Number.NaN;
            var serverGlassValue = Number(@json(session('glass_transparency', 40)));
            var serverGlass = Number(@json(session('glass_control_version', 1))) === 2 ? serverGlassValue : 100 - serverGlassValue;
            var glass = Number.isFinite(storedGlass) && storedGlass >= 0 && storedGlass <= 100 ? storedGlass : serverGlass;
            var glassRatio = Math.min(100, Math.max(0, glass)) / 100;
            var glassActive = liquidDesignEnabled;
            document.documentElement.dataset.theme = theme;
            document.documentElement.dataset.accentTheme = accent;
            document.documentElement.dataset.liquidDesign = liquidDesignEnabled ? 'on' : 'off';
            document.documentElement.dataset.glassMaterial = 'glass';
            document.documentElement.style.colorScheme = theme;
            document.documentElement.style.setProperty('--glass-user-transparency', glassActive ? glassRatio.toFixed(2) : '0');
            document.documentElement.style.setProperty('--glass-opacity', glassActive ? (0.54 + (glassRatio * 0.28)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-nav-opacity', glassActive ? (0.62 + (glassRatio * 0.26)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-nav-opacity-dark', glassActive ? (0.68 + (glassRatio * 0.20)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-header-opacity', glassActive ? (0.60 + (glassRatio * 0.26)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-header-opacity-dark', glassActive ? (0.66 + (glassRatio * 0.21)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-popup-opacity', glassActive ? (0.76 + (glassRatio * 0.16)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-popup-opacity-dark', glassActive ? (0.82 + (glassRatio * 0.13)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-drawer-opacity', glassActive ? (0.62 + (glassRatio * 0.27)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-drawer-opacity-dark', glassActive ? (0.68 + (glassRatio * 0.21)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-browser-drawer-opacity', glassActive ? (0.84 + (glassRatio * 0.11)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-browser-drawer-opacity-dark', glassActive ? (0.86 + (glassRatio * 0.09)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--myhep-user-blur', glassActive ? (8 + (glassRatio * 24)) + 'px' : '0px');
            document.documentElement.style.setProperty('--myhep-preview-blur', glassActive ? (1 + (glassRatio * glassRatio * 23)) + 'px' : '0px');
            document.documentElement.style.setProperty('--student-nav-material-alpha', glassActive ? (0.68 + (glassRatio * 0.20)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--student-nav-active-alpha', glassActive ? (0.58 + (glassRatio * 0.22)).toFixed(2) : '1');
            document.documentElement.style.setProperty('--student-nav-reflection-alpha', glassActive ? (0.34 - (glassRatio * 0.10)).toFixed(2) : '0');
            document.documentElement.style.setProperty('--student-nav-active-reflection-alpha', glassActive ? (0.46 - (glassRatio * 0.14)).toFixed(2) : '0');
            document.documentElement.style.setProperty('--student-nav-blur', glassActive ? (6 + (glassRatio * 24)) + 'px' : '0px');
            document.documentElement.style.setProperty('--student-nav-active-blur', glassActive ? (4 + (glassRatio * 17)) + 'px' : '0px');
            document.documentElement.style.setProperty('--student-nav-saturation', glassActive ? Math.round(300 - (glassRatio * 200)) + '%' : '100%');
            document.documentElement.style.setProperty('--myhep-edge-prism-alpha', glassActive ? (0.38 - (glassRatio * 0.25)).toFixed(2) : '0');
            document.documentElement.dataset.glassTransparency = String(glass);
            document.documentElement.dataset.glassHigh = glassActive && glass <= 30 ? 'true' : 'false';
        } catch (error) {
            document.documentElement.dataset.theme = @json(session('theme', 'light'));
            document.documentElement.dataset.accentTheme = @json(session('accent_theme', 'gold'));
            document.documentElement.dataset.liquidDesign = @json($themeLiquidDesignEnabled) ? 'on' : 'off';
            document.documentElement.dataset.glassMaterial = 'glass';
            document.documentElement.style.setProperty('--glass-opacity', @json($themeLiquidDesignEnabled) ? '.65' : '1');
            document.documentElement.style.setProperty('--myhep-nav-opacity', @json($themeLiquidDesignEnabled) ? '.72' : '1');
            document.documentElement.style.setProperty('--myhep-nav-opacity-dark', @json($themeLiquidDesignEnabled) ? '.76' : '1');
            document.documentElement.style.setProperty('--myhep-header-opacity', @json($themeLiquidDesignEnabled) ? '.70' : '1');
            document.documentElement.style.setProperty('--myhep-header-opacity-dark', @json($themeLiquidDesignEnabled) ? '.74' : '1');
            document.documentElement.style.setProperty('--myhep-popup-opacity', @json($themeLiquidDesignEnabled) ? '.87' : '1');
            document.documentElement.style.setProperty('--myhep-popup-opacity-dark', @json($themeLiquidDesignEnabled) ? '.90' : '1');
            document.documentElement.style.setProperty('--myhep-drawer-opacity', @json($themeLiquidDesignEnabled) ? '.73' : '1');
            document.documentElement.style.setProperty('--myhep-drawer-opacity-dark', @json($themeLiquidDesignEnabled) ? '.76' : '1');
            document.documentElement.style.setProperty('--myhep-browser-drawer-opacity', @json($themeLiquidDesignEnabled) ? '.88' : '1');
            document.documentElement.style.setProperty('--myhep-browser-drawer-opacity-dark', @json($themeLiquidDesignEnabled) ? '.90' : '1');
            document.documentElement.style.setProperty('--myhep-preview-blur', @json($themeLiquidDesignEnabled) ? '4.7px' : '0px');
            document.documentElement.style.setProperty('--myhep-user-blur', @json($themeLiquidDesignEnabled) ? '16px' : '0px');
            document.documentElement.style.setProperty('--student-nav-material-alpha', @json($themeLiquidDesignEnabled) ? '.76' : '1');
            document.documentElement.style.setProperty('--student-nav-active-alpha', @json($themeLiquidDesignEnabled) ? '.74' : '1');
            document.documentElement.style.setProperty('--student-nav-reflection-alpha', @json($themeLiquidDesignEnabled) ? '.26' : '0');
            document.documentElement.style.setProperty('--student-nav-active-reflection-alpha', @json($themeLiquidDesignEnabled) ? '.34' : '0');
            document.documentElement.style.setProperty('--student-nav-blur', @json($themeLiquidDesignEnabled) ? '22px' : '0px');
            document.documentElement.style.setProperty('--student-nav-active-blur', @json($themeLiquidDesignEnabled) ? '12px' : '0px');
            document.documentElement.style.setProperty('--student-nav-saturation', @json($themeLiquidDesignEnabled) ? '220%' : '100%');
            document.documentElement.style.setProperty('--myhep-edge-prism-alpha', @json($themeLiquidDesignEnabled) ? '.28' : '0');
        }
    })();
</script>
