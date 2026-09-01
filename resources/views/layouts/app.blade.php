<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.theme_bootstrap')
    <meta name="theme-color" content="#171412">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>@yield('title', config('app.name', 'MyHEP'))</title>
    @include('partials.brand_icons')
    <meta name="push-public-key" content="{{ config('services.webpush.public_key') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --app-safe-top: env(safe-area-inset-top, 0px);
        }
    </style>

    @php
        $studentEdgePushConfig = [
            'enabled' => myhepWebPushEnabled(),
            'publicKey' => config('services.webpush.public_key'),
            'subscribeUrl' => route('push.subscribe'),
            'unsubscribeUrl' => route('push.unsubscribe'),
            'authenticated' => session()->has('auth_user'),
            'prompt' => [
                'kicker' => __('Notifications'),
                'title' => __('Turn on push notifications'),
                'copy' => __('Get instant alerts when fines, stickers, and important account updates happen.'),
                'enable' => __('Enable notifications'),
                'later' => __('Maybe later'),
            ],
        ];
        $studentEdgeUiConfig = [
            'authenticated' => session()->has('auth_user'),
            'notificationUrl' => route('notifications.feed'),
            'labels' => [
                'notifications' => __('Notifications'),
                'notificationEmpty' => __('There are no notifications to show.'),
                'notificationError' => __('Notifications could not be loaded. Try again.'),
                'filters' => __('Filters'),
                'closeFilters' => __('Close filters'),
                'mediaPreview' => __('File preview'),
                'openOriginal' => __('Open original'),
                'download' => __('Download'),
                'close' => __('Close'),
                'loading' => __('Loading'),
            ],
        ];
    @endphp
    <script>
        window.studentEdgePush = {!! json_encode($studentEdgePushConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
        window.studentEdgeUi = {!! json_encode($studentEdgeUiConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
    </script>

        @vite(['resources/css/app.css', 'resources/css/design-system.css', 'resources/css/liquid-glass.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $authUser = session('auth_user');
    $isStudent = ($authUser['role'] ?? null) === 'student';
    $isAdmin = ($authUser['role'] ?? null) === 'admin';
    $adminScope = $authUser['admin_role'] ?? null;
    $authInitials = strtoupper(substr(trim((string) ($authUser['name'] ?? 'U')), 0, 2));
    $authAvatarUrl = null;
    $authStaffPosition = null;
    $authTable = $isAdmin ? 'admins' : ($isStudent ? 'students' : null);
    if ($authTable
        && !empty($authUser['id'])
        && \Illuminate\Support\Facades\Schema::hasTable($authTable)
        && \Illuminate\Support\Facades\Schema::hasColumn($authTable, 'photo')) {
        $authPhotoPath = trim((string) \Illuminate\Support\Facades\DB::table($authTable)
            ->where('id', $authUser['id'])
            ->value('photo'));
        if ($authPhotoPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($authPhotoPath)) {
            $authAvatarUrl = asset('storage/' . ltrim($authPhotoPath, '/'));
        }
    }
    if ($isAdmin
        && !empty($authUser['id'])
        && \Illuminate\Support\Facades\Schema::hasTable('admins')
        && \Illuminate\Support\Facades\Schema::hasColumn('admins', 'position')) {
        $authStaffPosition = trim((string) \Illuminate\Support\Facades\DB::table('admins')
            ->where('id', $authUser['id'])
            ->value('position'));
    }
    $sidebarRoleLabel = $isAdmin && $adminScope
        ? ($authStaffPosition !== '' ? $authStaffPosition : adminRoleLabel($adminScope))
        : null;
    $isScholarshipAdmin = $isAdmin && adminCan('scholarship');
    $isDisciplineAdmin = $isAdmin && adminCan('discipline');
    $isMovementAdmin = $isAdmin && adminCan('movement');
    $isDocumentAdmin = $isAdmin && adminCan('documents');
    $isGuardAdmin = $isAdmin && $adminScope === 'guard';
    $isLecturerAdmin = $isAdmin && $adminScope === 'lecturer';
    $sidebarAccountType = $isLecturerAdmin ? __('Staff') : ($authUser['role'] ?? '-');
    $hasStaffOverride = $isLecturerAdmin && (bool) ($authUser['staff_override'] ?? false) && !empty($authUser['linked_admin_id']);
    $isStudentAffairsHead = $isAdmin && $adminScope === 'student_affairs_head';
    $lecturerPages = $isLecturerAdmin ? app(\App\Support\LecturerPageAccess::class) : null;
    $lecturerCanListOffenses = $isLecturerAdmin && $lecturerPages->enabled((int) ($authUser['id'] ?? 0), 'offense_list');
    $lecturerCanRegisterOffense = $isLecturerAdmin && $lecturerPages->enabled((int) ($authUser['id'] ?? 0), 'offense_register');
    $lecturerCanManageGuards = $isLecturerAdmin && $lecturerPages->enabled((int) ($authUser['id'] ?? 0), 'guard_management');
    $canManageGuards = $isAdmin && adminCan('guards.manage') && (!$isLecturerAdmin || $lecturerCanManageGuards);
    $canUseLaptops = $isAdmin && adminCan('laptops.use');
    $canManageLaptops = $isAdmin && adminCan('laptops.manage');
    $hasAdminOverride = $isStudent && (bool) ($authUser['admin_override'] ?? false) && !empty($authUser['linked_admin_id']);
    $studentOnDashboard = request()->routeIs('student.dashboard');
    $adminOnDashboard = request()->routeIs('admin.dashboard');
    $studentOnScholarship = request()->routeIs('student.scholarships.*')
        || request()->routeIs('student.scholarships.announcements')
        || request()->routeIs('student.scholarship-status.*')
        || request()->routeIs('student.foodbank.*');
    $studentOnDiscipline = request()->routeIs('student.offenses.*')
        || request()->routeIs('student.rules.*')
        || request()->routeIs('student.vehicle-stickers.*')
        || request()->routeIs('student.movements.*')
        || request()->routeIs('student.discipline-announcements.*');
    $studentCanPresentAttendanceQr = $isStudent
        && !empty($authUser['id'])
        && \App\Http\Controllers\Student\ProgramActivityController::studentHasQrPresenterAccess((int) $authUser['id']);
    $adminOnDiscipline = request()->routeIs('admin.offenses.*')
        || request()->routeIs('admin.vehicle-stickers.*')
        || request()->routeIs('admin.insurance.*')
        || request()->routeIs('admin.movements.*')
        || request()->routeIs('admin.program-participation-points.*')
        || request()->routeIs('admin.discipline-announcements.*')
        || request()->routeIs('admin.rules.*');
    $adminOnScholarship = request()->routeIs('admin.scholarships.*')
        || request()->routeIs('admin.student-scholarship-status.*')
        || request()->routeIs('admin.welfare.*')
        || request()->routeIs('admin.foodbank.*')
        || request()->routeIs('admin.scholarship-announcements.*');
    // The student dashboard keeps its desktop canvas clear, but still provides
    // the normal sidebar drawer and hamburger control on mobile.
    $showSidebar = $isAdmin || $isStudent;
    $showDesktopSidebar = $isAdmin || ($isStudent && !$studentOnDashboard);
    $showHeaderUserMenu = (bool) $authUser && ($isStudent || $adminOnDashboard);
    $systemFeatures = app(\App\Support\SystemFeatures::class);
    $showStudentBottomNav = $isStudent;
    $showStaffBottomNav = $isLecturerAdmin;
    $studentBrowserBottomNavEnabled = $systemFeatures->enabled('student_browser_bottom_nav');
    $studentAiHelperEnabled = $systemFeatures->enabled('student_ai_helper');
    $lecturerAiHelperEnabled = $systemFeatures->enabled('lecturer_ai_helper');
    $adminAiHelperEnabled = $adminScope === 'system_admin' || $systemFeatures->enabled('admin_ai_helper');
    $adminLiquidDesignEnabled = ! $isAdmin || $systemFeatures->adminLiquidDesignEnabled($adminScope);
    $studentMoreActive = request()->routeIs('student.movements.index')
        || request()->routeIs('student.documents.*')
        || request()->routeIs('student.vehicle-stickers.*')
        || request()->routeIs('student.rules.*')
        || request()->routeIs('student.discipline-announcements.*')
        || request()->routeIs('settings.*');
    $bodyClasses = trim(
        ($isStudent ? 'role-student student-mobile-shell' : '') . ' ' .
        ($isStudent && request()->routeIs('student.scholarships.index') ? 'student-liquid-aid' : '') . ' ' .
        ($isStudent && request()->routeIs('student.offenses.index') ? 'student-liquid-fines' : '') . ' ' .
        (($showStudentBottomNav || $showStaffBottomNav) ? 'student-bottom-nav-pwa-eligible' : '') . ' ' .
        ((($showStudentBottomNav && $studentBrowserBottomNavEnabled) || $showStaffBottomNav) ? 'student-bottom-nav-eligible' : '') . ' ' .
        (request()->routeIs('student.movements.scan', 'admin.laptops.scan') ? 'student-scan-mode ' : '') .
        ($isStudent && $studentOnDashboard ? 'student-dashboard-mobile-sidebar ' : '') .
        ($isAdmin && $adminScope === 'system_admin' ? 'role-system-admin system-admin-shell ' : '') .
        (! $adminLiquidDesignEnabled ? 'admin-liquid-disabled ' : '') .
        ($adminOnDashboard ? 'admin-dashboard-page ' : '') .
        (request()->routeIs('admin.ai-helper.*', 'student.ai-helper.*', 'lecturer.ai-helper.*') ? 'admin-ai-helper-page ' : '')
    );
@endphp
<body data-theme="{{ session('theme', 'light') }}" data-accent-theme="{{ session('accent_theme', 'gold') }}" class="{{ $bodyClasses }}">
<div class="app-layout">

    @if($showSidebar)
    <aside class="sidebar" id="appSidebar" role="navigation" aria-label="{{ __('Navigasi utama') }}">
        <div class="sb-header">
            <a href="{{ route('home') }}" class="sb-brand">
                <div class="sb-brand-icon">
                    <img src="{{ asset('images/myhep-mark.png') }}?v=11" alt="{{ __('Logo MyHEP') }}">
                </div>
                <div><div class="sb-brand-name">MyHEP</div><div class="sb-brand-sub">{{ __('Student Affairs') }}</div></div>
            </a>
            <button class="sb-close" id="sbClose" aria-label="{{ __('Tutup sidebar') }}"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <div class="sb-user">
            <div class="sb-user-row">
                @include('partials.auth_avatar', ['class' => 'sb-avatar', 'url' => $authAvatarUrl, 'initials' => $authInitials])
                <div style="min-width:0">
                    <div class="sb-user-name">{{ $authUser['name'] ?? __('Pengguna') }}</div>
                    <div class="sb-user-role" @if($sidebarRoleLabel) title="{{ $sidebarAccountType.' - '.$sidebarRoleLabel }}" @endif>{{ $sidebarAccountType }}{{ $sidebarRoleLabel ? ' - '.$sidebarRoleLabel : '' }}</div>
                </div>
            </div>
            @if($isStudent)
                <span class="sb-role-badge student">{{ __('Pelajar') }}</span>
            @elseif($isAdmin)
                <span class="sb-role-badge admin">{{ $isLecturerAdmin ? __('Staff') : __('Admin') }}</span>
            @endif
        </div>

        <div class="sb-scroll" tabindex="0" role="region" aria-label="{{ __('Navigasi utama') }}" data-lenis-prevent>
            <div class="sb-scroll-inner">
            @if($isStudent)
                <div class="nav-label">{{ __('ui.main_menu') }}</div>
                <nav>
                    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2 7-7 7 7"/></svg>
                        {{ __('Index') }}
                    </a>
                    <a href="{{ route('student.programs.index') }}" class="nav-link {{ request()->routeIs('student.programs.*') && !request()->routeIs('student.programs.attendance-qr.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4zM8 3v6m8-6v6"/></svg>
                        {{ __('Program Activities') }}
                    </a>
                    @if($studentCanPresentAttendanceQr)
                    <a href="{{ route('student.programs.attendance-qr.index') }}" class="nav-link {{ request()->routeIs('student.programs.attendance-qr.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM19 14h2v7h-7v-2h5z"/></svg>
                        {{ __('Attendance QR') }}
                    </a>
                    @endif
                    @if($studentAiHelperEnabled)
                    <a href="{{ route('student.ai-helper.index') }}" class="nav-link {{ request()->routeIs('student.ai-helper.*') ? 'active' : '' }}">
                        @include('partials.ai_helper_icon', ['class' => 'nav-icon'])
                        {{ __('AI Helper') }}
                    </a>
                    @else
                    <span class="nav-link" style="opacity:.55;cursor:not-allowed" aria-disabled="true">
                        @include('partials.ai_helper_icon', ['class' => 'nav-icon'])
                        {{ __('AI Helper') }} · {{ __('Unavailable') }}
                    </span>
                    @endif
                </nav>

                @if($studentOnScholarship)
                    <div class="nav-label">{{ __('Scholarship') }}</div>
                    <nav>
                        <a href="{{ route('student.scholarships.index') }}" class="nav-link {{ request()->routeIs('student.scholarships.index') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                            {{ __('Scholarship') }}
                        </a>
                        <a href="{{ route('student.scholarships.announcements') }}" class="nav-link {{ request()->routeIs('student.scholarships.announcements') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h9m-9 3h5.25M3.75 6.75A2.25 2.25 0 016 4.5h12A2.25 2.25 0 0120.25 6.75v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z"/></svg>
                            {{ __('Pengumuman Biasiswa') }}
                        </a>
                        <a href="{{ route('student.scholarship-status.form') }}" class="nav-link {{ request()->routeIs('student.scholarship-status.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-7.5A2.25 2.25 0 014.5 17.25V6.75A2.25 2.25 0 016.75 4.5h7.5A2.25 2.25 0 0116.5 6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.5M8.25 12.75h4.5"/></svg>
                            {{ __('Borang Status Biasiswa') }}
                        </a>
                        <a href="{{ route('student.foodbank.index') }}" class="nav-link {{ request()->routeIs('student.foodbank.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                            {{ __('Food Bank Siswa') }}
                        </a>
                    </nav>
                @endif

                @if($studentOnDiscipline)
                    <div class="nav-label">{{ __('Disiplin') }}</div>
                    <nav>
                        <a href="{{ route('student.offenses.index') }}" class="nav-link {{ request()->routeIs('student.offenses.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 6h10"/></svg>
                            {{ __('My Offenses') }}
                        </a>
                        <a href="{{ route('student.vehicle-stickers.index') }}" class="nav-link {{ request()->routeIs('student.vehicle-stickers.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l1.5-4.5a2.25 2.25 0 012.136-1.54h9.228A2.25 2.25 0 0118.75 9l1.5 4.5M5.25 13.5h13.5M6 16.5h.75m10.5 0H18m-12 0a.75.75 0 00-.75.75v.75c0 .414.336.75.75.75h.75a.75.75 0 00.75-.75v-.75a.75.75 0 00-.75-.75H6zm10.5 0a.75.75 0 00-.75.75v.75c0 .414.336.75.75.75h.75a.75.75 0 00.75-.75v-.75a.75.75 0 00-.75-.75h-.75z"/></svg>
                            {{ __('Vehicle Sticker') }}
                        </a>
                        <a href="{{ route('student.movements.index') }}" class="nav-link {{ request()->routeIs('student.movements.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m4-2a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                            {{ __('Student Movement') }}
                        </a>
                        <a href="{{ route('student.discipline-announcements.index') }}" class="nav-link {{ request()->routeIs('student.discipline-announcements.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h9m-9 3h5.25M3.75 6.75A2.25 2.25 0 016 4.5h12A2.25 2.25 0 0120.25 6.75v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z"/></svg>
                            {{ __('Announcements') }}
                        </a>
                        <a href="{{ route('student.rules.index') }}" class="nav-link {{ request()->routeIs('student.rules.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75H7.5A2.25 2.25 0 005.25 9v9A2.25 2.25 0 007.5 20.25h9A2.25 2.25 0 0018.75 18V9A2.25 2.25 0 0016.5 6.75H12z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 11.25h6M9 14.25h6"/></svg>
                            {{ __('Rules') }}
                        </a>
                    </nav>
                @endif

                @if(!$studentOnScholarship && !$studentOnDiscipline)
                    <div class="nav-label">{{ __('Portal Pelajar') }}</div>
                    <nav>
                        <details class="nav-group">
                            <summary class="nav-link nav-group-toggle">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                {{ __('Scholarship') }}
                                <svg class="nav-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                            </summary>
                            <div class="nav-submenu">
                                <a href="{{ route('student.scholarships.index') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                    {{ __('Scholarship Records') }}
                                </a>
                                <a href="{{ route('student.scholarships.announcements') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h9m-9 3h5.25M3.75 6.75A2.25 2.25 0 016 4.5h12A2.25 2.25 0 0120.25 6.75v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z"/></svg>
                                    {{ __('Scholarship Announcements') }}
                                </a>
                                <a href="{{ route('student.scholarship-status.form') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-7.5A2.25 2.25 0 014.5 17.25V6.75A2.25 2.25 0 016.75 4.5h7.5A2.25 2.25 0 0116.5 6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.5M8.25 12.75h4.5"/></svg>
                                    {{ __('Status Form') }}
                                </a>
                                <a href="{{ route('student.foodbank.index') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                    {{ __('Food Bank Siswa') }}
                                </a>
                            </div>
                        </details>
                        <details class="nav-group">
                            <summary class="nav-link nav-group-toggle">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                {{ __('Discipline') }}
                                <svg class="nav-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                            </summary>
                            <div class="nav-submenu">
                                <a href="{{ route('student.offenses.index') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 6h10"/></svg>
                                    {{ __('My Offenses') }}
                                </a>
                                <a href="{{ route('student.vehicle-stickers.index') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l1.5-4.5a2.25 2.25 0 012.136-1.54h9.228A2.25 2.25 0 0118.75 9l1.5 4.5M5.25 13.5h13.5M6 16.5h.75m10.5 0H18m-12 0a.75.75 0 00-.75.75v.75c0 .414.336.75.75.75h.75a.75.75 0 00.75-.75v-.75a.75.75 0 00-.75-.75H6zm10.5 0a.75.75 0 00-.75.75v.75c0 .414.336.75.75.75h.75a.75.75 0 00.75-.75v-.75a.75.75 0 00-.75-.75h-.75z"/></svg>
                                    {{ __('Vehicle Sticker') }}
                                </a>
                                <a href="{{ route('student.movements.index') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m4-2a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                                    {{ __('Student Movement') }}
                                </a>
                                <a href="{{ route('student.rules.index') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75H7.5A2.25 2.25 0 005.25 9v9A2.25 2.25 0 007.5 20.25h9A2.25 2.25 0 0018.75 18V9A2.25 2.25 0 0016.5 6.75H12z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 11.25h6M9 14.25h6"/></svg>
                                    {{ __('Rules') }}
                                </a>
                            </div>
                        </details>
                    </nav>
                @endif

                <div class="nav-label">{{ __('Account') }}</div>
                <nav>
                    <a href="{{ route('student.documents.index') }}" class="nav-link {{ request()->routeIs('student.documents.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h7.5L18 7.5v12.75H6.75z"/><path stroke-linecap="round" d="M9 11.25h6M9 14.25h6"/></svg>
                        {{ __('Document Centre') }}
                    </a>
                    <a href="{{ route('student.profile') }}" class="nav-link {{ request()->routeIs('student.profile*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        {{ __('Profile') }}
                    </a>
                    <a href="{{ route('bug-reports.create') }}" class="nav-link {{ request()->routeIs('bug-reports.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M4.5 19.5h15l-7.5-15-7.5 15z"/></svg>
                        {{ __('Report a Problem') }}
                    </a>
                    <a href="{{ route('settings.show') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v2.25l1.5 1.5"/></svg>
                        {{ __('Settings') }}
                    </a>
                    @if($hasAdminOverride)
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="admin">
                            <button type="submit" class="nav-link nav-system-controls" style="width:100%;cursor:pointer;font:inherit;">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 12h2M3 12h2M12 3v2m0 14v2"/></svg>
                                {{ __('System Controls') }}
                            </button>
                        </form>
                    @endif
                </nav>
            @elseif($isAdmin)
                <div class="nav-label">{{ __('Dashboard') }}</div>
                <nav>
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2 7-7 7 7"/></svg>
                        {{ __('Dashboard') }}
                    </a>
                    <a href="{{ route('admin.programs.index') }}" class="nav-link {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 016 4.5h4.5l1.5 1.5h6A2.25 2.25 0 0120.25 8.25v9A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25z"/><path stroke-linecap="round" d="M8.25 12h7.5m-7.5 3h4.5"/></svg>
                        {{ __('Program Management') }}
                    </a>
                    @if(!$isGuardAdmin)
                        <a href="{{ route('admin.reports.monthly') }}" class="nav-link {{ request()->routeIs('admin.reports.monthly') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v18h16.5M7.5 15l3-3 2.25 2.25L16.5 9"/></svg>
                            {{ __('Monthly Report') }}
                        </a>
                        @if($isLecturerAdmin && $lecturerAiHelperEnabled)
                            <a href="{{ route('lecturer.ai-helper.index') }}" class="nav-link {{ request()->routeIs('lecturer.ai-helper.*') ? 'active' : '' }}">
                                @include('partials.ai_helper_icon', ['class' => 'nav-icon'])
                                {{ __('AI Helper') }}
                            </a>
                        @elseif(!$isLecturerAdmin && $adminAiHelperEnabled)
                            <a href="{{ route('admin.ai-helper.index') }}" class="nav-link {{ request()->routeIs('admin.ai-helper.*') ? 'active' : '' }}">
                                @include('partials.ai_helper_icon', ['class' => 'nav-icon'])
                                {{ __('AI Helper') }}
                            </a>
                        @endif
                    @endif
                    @if(adminCan('students.list'))
                        <a href="{{ route('admin.students.index') }}" data-sidebar-student-list class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                            {{ __('Senarai Pelajar') }}
                        </a>
                    @endif
                </nav>

                @if($isGuardAdmin)
                    <div class="nav-label">{{ __('Guard House') }}</div>
                    <nav>
                        <a href="{{ route('admin.movements.qr') }}" class="nav-link {{ request()->routeIs('admin.movements.qr*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h4.5v4.5h-4.5zm12 0h4.5v4.5h-4.5zm-12 12h4.5v4.5h-4.5zm12 0h4.5v4.5h-4.5zM9 6h6M6 9v6M18 9v6M9 18h6"/></svg>
                            {{ __('Guard House QR') }}
                        </a>
                        <a href="{{ route('admin.movements.index') }}" class="nav-link {{ request()->routeIs('admin.movements.index') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m4-2a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                            {{ __('Student Movement') }}
                        </a>
                        <a href="{{ route('admin.movements.outside') }}" class="nav-link {{ request()->routeIs('admin.movements.outside') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            {{ __('Outside Campus') }}
                        </a>
                        <a href="{{ route('admin.movements.violations') }}" class="nav-link {{ request()->routeIs('admin.movements.violations') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M4.5 19.5h15l-7.5-15-7.5 15z"/></svg>
                            {{ __('Violations') }}
                        </a>
                    </nav>
                @endif

                @if($isScholarshipAdmin)
                    <nav>
                        @if(in_array($adminScope, ['system_admin', 'student_affairs_head'], true))
                            <details class="nav-group {{ $adminOnScholarship ? 'active' : '' }}" {{ $adminOnScholarship ? 'open' : '' }}>
                                <summary class="nav-link nav-group-toggle">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                    {{ __('Scholarship') }}
                                    <svg class="nav-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                                </summary>
                                <div class="nav-submenu">
                        @else
                            <div>
                        @endif
                                <a href="{{ route('admin.scholarships.index') }}" class="nav-link {{ request()->routeIs('admin.scholarships.index') || request()->routeIs('admin.scholarships.edit') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                    {{ __('Rekod Scholarship') }}
                                </a>
                                <a href="{{ route('admin.scholarships.b40-tvet') }}" class="nav-link {{ request()->routeIs('admin.scholarships.b40-tvet*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/></svg>
                                    {{ __('SCHOLARSHIP B40 TVET') }}
                                </a>
                                <a href="{{ route('admin.student-scholarship-status.index') }}" class="nav-link {{ request()->routeIs('admin.student-scholarship-status.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-7.5A2.25 2.25 0 014.5 17.25V6.75A2.25 2.25 0 016.75 4.5h7.5A2.25 2.25 0 0116.5 6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.5M8.25 12.75h4.5"/></svg>
                                    {{ __('Data Status Biasiswa') }}
                                </a>
                                <a href="{{ route('admin.welfare.index') }}" class="nav-link {{ request()->routeIs('admin.welfare.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    {{ __('Kebajikan Pelajar') }}
                                </a>
                                <a href="{{ route('admin.foodbank.index') }}" class="nav-link {{ request()->routeIs('admin.foodbank.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                    {{ __('Food Bank Siswa') }}
                                </a>
                                <a href="{{ route('admin.scholarship-announcements.index') }}" class="nav-link {{ request()->routeIs('admin.scholarship-announcements.index') || request()->routeIs('admin.scholarship-announcements.edit') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h9m-9 3h5.25M3.75 6.75A2.25 2.25 0 016 4.5h12A2.25 2.25 0 0120.25 6.75v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z"/></svg>
                                    {{ __('Pengumuman') }}
                                </a>
                                <a href="{{ route('admin.scholarship-announcements.create') }}" class="nav-link {{ request()->routeIs('admin.scholarship-announcements.create') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    {{ __('Tambah Pengumuman') }}
                                </a>
                            </div>
                        @if(in_array($adminScope, ['system_admin', 'student_affairs_head'], true))
                            </details>
                        @endif
                    </nav>
                @endif

                @if($isDisciplineAdmin)
                    <nav>
                        @if(in_array($adminScope, ['system_admin', 'student_affairs_head'], true))
                            <details class="nav-group {{ $adminOnDiscipline ? 'active' : '' }}" {{ $adminOnDiscipline ? 'open' : '' }}>
                                <summary class="nav-link nav-group-toggle">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M4.5 19.5h15l-7.5-15-7.5 15z"/></svg>
                                    {{ __('Discipline') }}
                                    <svg class="nav-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                                </summary>
                                <div class="nav-submenu">
                        @else
                            <div>
                        @endif
                                <a href="{{ route('admin.offenses.index') }}" class="nav-link {{ request()->routeIs('admin.offenses.index') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6"/></svg>
                                    {{ __('Senarai Kesalahan') }}
                                </a>
                                <a href="{{ route('admin.offenses.create') }}" class="nav-link {{ request()->routeIs('admin.offenses.create') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    {{ __('Daftar Kesalahan') }}
                                </a>
                                <a href="{{ route('admin.vehicle-stickers.index') }}" class="nav-link {{ request()->routeIs('admin.vehicle-stickers.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l1.5-4.5a2.25 2.25 0 012.136-1.54h9.228A2.25 2.25 0 0118.75 9l1.5 4.5M5.25 13.5h13.5M6 16.5h.75m10.5 0H18m-12 0a.75.75 0 00-.75.75v.75c0 .414.336.75.75.75h.75a.75.75 0 00.75-.75v-.75a.75.75 0 00-.75-.75H6zm10.5 0a.75.75 0 00-.75.75v.75c0 .414.336.75.75.75h.75a.75.75 0 00.75-.75v-.75a.75.75 0 00-.75-.75h-.75z"/></svg>
                                    {{ __('Sticker Kenderaan') }}
                                </a>
                                <a href="{{ route('admin.insurance.index') }}" class="nav-link {{ request()->routeIs('admin.insurance.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                    {{ __('Insurans Pelajar (Sem 3 & 5)') }}
                                </a>
                                <a href="{{ route('admin.movements.index') }}" class="nav-link {{ request()->routeIs('admin.movements.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m4-2a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                                    {{ __('Student Movement') }}
                                </a>
                                <a href="{{ route('admin.program-participation-points.index') }}" class="nav-link {{ request()->routeIs('admin.program-participation-points.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l2.4 4.86 5.36.78-3.88 3.78.92 5.34L12 15.24 7.2 17.76l.92-5.34L4.24 8.64l5.36-.78L12 3z"/></svg>
                                    {{ __('Program Merit Points') }}
                                </a>
                                <a href="{{ route('admin.discipline-announcements.index') }}" class="nav-link {{ request()->routeIs('admin.discipline-announcements.index') || request()->routeIs('admin.discipline-announcements.edit') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h9m-9 3h5.25M3.75 6.75A2.25 2.25 0 016 4.5h12A2.25 2.25 0 0120.25 6.75v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z"/></svg>
                                    {{ __('Pengumuman Disiplin') }}
                                </a>
                                <a href="{{ route('admin.discipline-announcements.create') }}" class="nav-link {{ request()->routeIs('admin.discipline-announcements.create') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    {{ __('Tambah Pengumuman') }}
                                </a>
                                <a href="{{ route('admin.rules.index') }}" class="nav-link {{ request()->routeIs('admin.rules.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75H7.5A2.25 2.25 0 005.25 9v9A2.25 2.25 0 007.5 20.25h9A2.25 2.25 0 0018.75 18V9A2.25 2.25 0 0016.5 6.75H12z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 11.25h6M9 14.25h6"/></svg>
                                    {{ __('Peraturan') }}
                                </a>
                            </div>
                        @if(in_array($adminScope, ['system_admin', 'student_affairs_head'], true))
                            </details>
                        @endif
                    </nav>
                @endif

                @if($isLecturerAdmin && ($lecturerCanListOffenses || $lecturerCanRegisterOffense))
                    <div class="nav-label">{{ __('Staff') }}</div>
                    <nav>
                        @if($lecturerCanListOffenses)
                            <a href="{{ route('admin.offenses.index') }}" class="nav-link {{ request()->routeIs('admin.offenses.index') ? 'active' : '' }}">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6"/></svg>
                                {{ __('Senarai Kesalahan') }}
                            </a>
                        @endif
                        @if($lecturerCanRegisterOffense)
                            <a href="{{ route('admin.offenses.create') }}" class="nav-link {{ request()->routeIs('admin.offenses.create') ? 'active' : '' }}">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                {{ __('Daftar Kesalahan') }}
                            </a>
                        @endif
                    </nav>
                @endif

                @if($isDocumentAdmin)
                    <div class="nav-label">{{ __('Documents') }}</div>
                    <nav>
                        <a href="{{ route('admin.insurance.index') }}" class="nav-link {{ request()->routeIs('admin.insurance.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            {{ __('Insurans Pelajar (Sem 3 & 5)') }}
                        </a>
                        <a href="{{ route('admin.documents.index') }}" class="nav-link {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h7.5L18 7.5v12.75H6.75z"/><path stroke-linecap="round" d="M9 11.25h6M9 14.25h6"/></svg>
                            {{ __('Student Documents') }}
                        </a>
                    </nav>
                @endif

                @if(in_array($adminScope, ['system_admin', 'student_affairs_head'], true))
                    <div class="nav-label">{{ __('Sistem') }}</div>
                    <nav>
                        @if($adminScope === 'system_admin')
                            <a href="{{ route('admin.maintenance.index') }}" class="nav-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.83-5.83M11.42 15.17l2.496-3.03a3.375 3.375 0 00-4.773-4.773L6.113 9.864m5.307 5.307L9.864 6.113m0 0L4.5 3.75 3.75 4.5l2.363 5.364m3.751-3.751L15.17 11.42"/></svg>
                                {{ __('Maintenance') }}
                            </a>
                            <a href="{{ route('admin.features.index') }}" class="nav-link {{ request()->routeIs('admin.features.*') ? 'active' : '' }}">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M5 7h14M5 12h14M5 17h14"/><circle cx="9" cy="7" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="10" cy="17" r="2"/></svg>
                                {{ __('Feature Controls') }}
                            </a>
                        @endif
                        <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.742-1.34 9.04 9.04 0 00-2.983-3.163m-1.358 5.663A9.035 9.035 0 0112 21a9.035 9.035 0 01-5.401-1.68m10.802 0a9.035 9.035 0 00-10.802 0M6.599 19.32a9.04 9.04 0 01-2.983-3.16A9.095 9.095 0 007.358 14.82m11.384-.44a9.05 9.05 0 00-15.484 0m15.484 0A9.03 9.03 0 0012 12c-2.305 0-4.41.867-6 2.38m12.742 0A9.03 9.03 0 0112 12m0 0a3 3 0 100-6 3 3 0 000 6z"/></svg>
                            {{ __('Staff Management') }}
                        </a>
                        @if($adminScope === 'system_admin')
                            <a href="{{ route('admin.admin-users.index') }}" class="nav-link {{ request()->routeIs('admin.admin-users.*') ? 'active' : '' }}">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75a4.5 4.5 0 014.5 4.5v1.5h.75A2.25 2.25 0 0119.5 12v6.75A2.25 2.25 0 0117.25 21H6.75A2.25 2.25 0 014.5 18.75V12a2.25 2.25 0 012.25-2.25h.75v-1.5a4.5 4.5 0 014.5-4.5z"/></svg>
                                {{ __('Admin Management') }}
                            </a>
                            <a href="{{ route('admin.active-visitors.index') }}" class="nav-link {{ request()->routeIs('admin.active-visitors.*') ? 'active' : '' }}">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M12 12a3.375 3.375 0 100-6.75 3.375 3.375 0 000 6.75zM3.75 20.25a8.25 8.25 0 0116.5 0"/></svg>
                                {{ __('Active Visitors') }}
                            </a>
                            <a href="{{ route('admin.bug-reports.index') }}" class="nav-link {{ request()->routeIs('admin.bug-reports.*') ? 'active' : '' }}">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6m-7.5-7.5h8.25L18 7.5v12.75A2.25 2.25 0 0115.75 22.5h-9A2.25 2.25 0 014.5 20.25V6A2.25 2.25 0 016.75 3.75H6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3.75V7.5H18"/></svg>
                                {{ __('bug_reports.nav_label') }}
                            </a>
                        @endif
                    </nav>
                @endif
                @if($canUseLaptops || $canManageGuards)
                    <div class="nav-label">{{ __('Operations') }}</div>
                    <nav>
                        @if($canUseLaptops)
                            <a href="{{ route('admin.laptops.scan') }}" class="nav-link {{ request()->routeIs('admin.laptops.scan*') ? 'active' : '' }}">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4z"/><path d="M14 14h2m2 0h2m-6 4h6"/></svg>
                                {{ __('Scan Laptop QR') }}
                            </a>
                        @endif
                        @if($canManageLaptops)
                            <a href="{{ route('admin.laptops.index') }}" class="nav-link {{ request()->routeIs('admin.laptops.index') ? 'active' : '' }}">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="12" rx="2"/><path d="M2 20h20"/></svg>
                                {{ __('Laptop Management') }}
                            </a>
                        @endif
                        @if($canManageGuards)
                        <a href="{{ route('admin.guards.index') }}" class="nav-link {{ request()->routeIs('admin.guards.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7.5 3v5.25c0 4.35-3.05 8.1-7.5 9.75-4.45-1.65-7.5-5.4-7.5-9.75V6L12 3z"/></svg>
                            {{ __('Guard Management') }}
                        </a>
                        @endif
                    </nav>
                @endif
                <div class="nav-label">{{ __('ui.sidebar_account') }}</div>
                <nav>
                    <a href="{{ route('admin.profile') }}" class="nav-link {{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.1a7.5 7.5 0 0115 0"/></svg>
                        {{ __('Profile') }}
                    </a>
                    <a href="{{ route('bug-reports.create') }}" class="nav-link {{ request()->routeIs('bug-reports.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M4.5 19.5h15l-7.5-15-7.5 15z"/></svg>
                        {{ __('Report a Problem') }}
                    </a>
                    <a href="{{ route('settings.show') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v2.25l1.5 1.5"/></svg>
                        {{ __('ui.settings') }}
                    </a>
                    @if($hasStaffOverride)
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="admin">
                            <button type="submit" class="nav-link nav-system-controls" style="width:100%;cursor:pointer;font:inherit;">
                                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 7 4 12l5 5"/><path d="M4 12h10a6 6 0 0 0 6-6"/></svg>
                                {{ __('Return to System Admin') }}
                            </button>
                        </form>
                    @endif
                </nav>
            @endif
            </div>
        </div>

        <div class="sb-footer">
            @include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--wide'])
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </aside>

    <div class="sb-overlay" id="sbOverlay" aria-hidden="true"></div>
    @endif

    <div class="main-wrap {{ $showDesktopSidebar ? 'has-sidebar' : 'no-sidebar' }}{{ $isStudent && $studentOnDashboard ? ' student-dashboard-mobile-sidebar-shell' : '' }}">
        @if($showSidebar)
        <div class="topbar">
            <button class="btn-ham" id="sbToggle" aria-label="{{ __('Buka sidebar') }}" aria-expanded="false" aria-controls="appSidebar">
                <div class="ham-box" id="hamBox"><span class="ham-line"></span><span class="ham-line"></span><span class="ham-line"></span></div>
            </button>
            <div class="topbar-brand">
                <span class="topbar-brand-mark">
                    <img src="{{ asset('images/myhep-mark.png') }}?v=11" alt="MyHEP">
                </span>
                <span class="topbar-brand-copy">
                    <span class="topbar-title">MyHEP</span>
                    <span class="topbar-subtitle">{{ __('Student Affairs') }}</span>
                </span>
            </div>
            <div class="topbar-actions">
                @include('partials.notification_button', ['notificationButtonClass' => 'se-notification-trigger--topbar'])
                @if($showHeaderUserMenu && $isStudent)
                    <button type="button" class="header-user" id="headerUserBtn" aria-expanded="false" aria-haspopup="menu" title="{{ $authUser['name'] ?? __('User') }}">
                        @include('partials.auth_avatar', ['class' => 'header-user-avatar', 'url' => $authAvatarUrl, 'initials' => $authInitials])
                        <span class="header-user-meta">
                            <span class="header-user-name">{{ $authUser['name'] ?? __('User') }}</span>
                            <span class="header-user-role">{{ $isAdmin ? adminRoleLabel($authUser['admin_role'] ?? null) : ($authUser['role'] ?? '-') }}</span>
                        </span>
                    </button>
                @endif
            </div>
        </div>
        @endif

        <div class="main-scroll-viewport" data-main-scroll>
        <div class="main-scroll-inner">

        @hasSection('header')
            <div class="page-header{{ $showHeaderUserMenu ? ' has-user-menu' : '' }}{{ $isAdmin && $adminScope === 'system_admin' ? ' page-header--system-liquid' : '' }}">
                <div class="page-header-inner">
                    <div class="page-header-left">
                        <span class="page-header-kicker">{{ __('Current page') }}</span>
                        <div class="page-header-title">@yield('header')</div>
                    </div>
                    @if($authUser)
                        <div class="page-header-right">
                            @include('partials.notification_button', ['notificationButtonClass' => 'se-notification-trigger--header'])
                            @if($showHeaderUserMenu && !$isStudent)
                                <a href="mailto:support@polibesut.edu.my?subject=MyHEP%20Support" class="header-support">
                                    {{ __('Support') }}
                                </a>
                                <button type="button" class="header-user" id="headerUserBtn" aria-expanded="false" aria-haspopup="menu">
                                    @include('partials.auth_avatar', ['class' => 'header-user-avatar', 'url' => $authAvatarUrl, 'initials' => $authInitials])
                                    <span class="header-user-meta">
                                        <span class="header-user-name">{{ $authUser['name'] ?? __('User') }}</span>
                                        <span class="header-user-role">{{ $isAdmin ? adminRoleLabel($authUser['admin_role'] ?? null) : ($authUser['role'] ?? '-') }}</span>
                                    </span>
                                </button>
                                <div class="header-user-menu" id="headerUserMenu" role="menu" aria-label="{{ __('User menu') }}">
                                    <div class="header-menu-head">
                                        @include('partials.auth_avatar', ['class' => 'header-user-avatar', 'url' => $authAvatarUrl, 'initials' => $authInitials])
                                        <span>
                                            <span class="header-menu-name">{{ $authUser['name'] ?? __('User') }}</span>
                                            <span class="header-menu-role">{{ $isAdmin ? adminRoleLabel($authUser['admin_role'] ?? null) : ($authUser['role'] ?? '-') }}</span>
                                        </span>
                                    </div>
                                    @if($isStudent)
                                        <a href="{{ route('student.profile') }}" class="header-menu-link">
                                            <span aria-hidden="true">&#9786;</span>{{ __('Profile') }}
                                        </a>
                                    @elseif($isAdmin)
                                        <a href="{{ route('admin.profile') }}" class="header-menu-link">
                                            <span aria-hidden="true">&#9786;</span>{{ __('Profile') }}
                                        </a>
                                    @endif
                                    <a href="{{ route('settings.show') }}" class="header-menu-link">
                                        <span aria-hidden="true">&#9881;</span>{{ __('Settings') }}
                                    </a>
                                    <a href="mailto:support@polibesut.edu.my?subject=MyHEP%20Support" class="header-menu-link">
                                        <span aria-hidden="true">?</span>{{ __('Support') }}
                                    </a>
                                    <div class="header-menu-sep"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="header-menu-btn logout">{{ __('Log Out') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <main class="page-body">@yield('content')</main>
        @include('partials.app_footer')
        </div>
        </div>
    </div>
</div>

@if($showHeaderUserMenu && $isStudent)
    <div class="header-user-menu header-user-menu--mobile" id="headerUserMenu" role="menu" aria-label="{{ __('User menu') }}">
        <div class="header-menu-head">
            @include('partials.auth_avatar', ['class' => 'header-user-avatar', 'url' => $authAvatarUrl, 'initials' => $authInitials])
            <span>
                <span class="header-menu-name">{{ $authUser['name'] ?? __('User') }}</span>
                <span class="header-menu-role">{{ $isAdmin ? adminRoleLabel($authUser['admin_role'] ?? null) : ($authUser['role'] ?? '-') }}</span>
            </span>
        </div>
        <a href="{{ route('student.profile') }}" class="header-menu-link">
            <span aria-hidden="true">&#9786;</span>{{ __('Profile') }}
        </a>
        <a href="{{ route('settings.show') }}" class="header-menu-link">
            <span aria-hidden="true">&#9881;</span>{{ __('Settings') }}
        </a>
        <a href="mailto:support@polibesut.edu.my?subject=MyHEP%20Support" class="header-menu-link">
            <span aria-hidden="true">?</span>{{ __('Support') }}
        </a>
        <div class="header-menu-sep"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="header-menu-btn logout">{{ __('Log Out') }}</button>
        </form>
    </div>
@endif

@if($showHeaderUserMenu)
    <button type="button" class="header-user-backdrop" id="headerUserBackdrop" aria-label="{{ __('Close user menu') }}" aria-hidden="true" tabindex="-1"></button>
@endif

@if($showStudentBottomNav)
    <button type="button" class="mobile-more-backdrop" id="mobileMoreBackdrop" aria-label="{{ __('Close menu') }}" aria-hidden="true" tabindex="-1"></button>
    <div class="mobile-more-sheet" id="mobileMoreSheet" role="dialog" aria-modal="true" aria-label="{{ __('More student services') }}" aria-hidden="true" tabindex="-1">
        <div class="mobile-more-sheet-head">
            <span>{{ __('More') }}</span>
            <small>{{ __('Student services') }}</small>
        </div>
        <a href="{{ route('student.movements.index') }}" class="mobile-more-link">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-4.4 7-11a7 7 0 0 0-14 0c0 6.6 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
            {{ __('Campus Movement') }}
        </a>
        <a href="{{ route('student.vehicle-stickers.index') }}" class="mobile-more-link">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 17h14l-1.5-6h-11z"/><path d="M7 17v2"/><path d="M17 17v2"/><path d="M7 11l1.5-4h7L17 11"/></svg></span>
            {{ __('Vehicle Sticker') }}
        </a>
        <a href="{{ route('student.rules.index') }}" class="mobile-more-link">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg></span>
            {{ __('Rules') }}
        </a>
        <a href="{{ route('student.discipline-announcements.index') }}" class="mobile-more-link">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m3 11 18-5v12L3 13z"/><path d="M11 14v5a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-6"/></svg></span>
            {{ __('Announcements') }}
        </a>
        <a href="{{ route('student.documents.index') }}" class="mobile-more-link">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6z"/><path d="M9 11h6M9 15h6"/></svg></span>
            {{ __('Document Centre') }}
        </a>
        <a href="{{ route('bug-reports.create') }}" class="mobile-more-link">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg></span>
            {{ __('Report a Problem') }}
        </a>
        <a href="{{ route('settings.show') }}" class="mobile-more-link">
            <span aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.8 1.8 0 0 0 .36 1.98l.04.04a2 2 0 1 1-2.83 2.83l-.04-.04A1.8 1.8 0 0 0 15 19.4a1.8 1.8 0 0 0-1 .6 1.8 1.8 0 0 0-.4 1.4V21a2 2 0 1 1-4 0v-.06a1.8 1.8 0 0 0-.4-1.4 1.8 1.8 0 0 0-1-.6 1.8 1.8 0 0 0-1.98.36l-.04.04a2 2 0 1 1-2.83-2.83l.04-.04A1.8 1.8 0 0 0 4.6 15a1.8 1.8 0 0 0-.6-1 1.8 1.8 0 0 0-1.4-.4H2a2 2 0 1 1 0-4h.06a1.8 1.8 0 0 0 1.4-.4 1.8 1.8 0 0 0 .6-1 1.8 1.8 0 0 0-.36-1.98l-.04-.04a2 2 0 1 1 2.83-2.83l.04.04A1.8 1.8 0 0 0 9 4.6a1.8 1.8 0 0 0 1-.6 1.8 1.8 0 0 0 .4-1.4V2a2 2 0 1 1 4 0v.06a1.8 1.8 0 0 0 .4 1.4 1.8 1.8 0 0 0 1 .6 1.8 1.8 0 0 0 1.98-.36l.04-.04a2 2 0 1 1 2.83 2.83l-.04.04A1.8 1.8 0 0 0 19.4 9c.25.36.6.66 1 .8.42.13.9.13 1.4 0H22a2 2 0 1 1 0 4h-.06a1.8 1.8 0 0 0-1.4.4c-.4.34-.66.7-.8 1Z"/></svg></span>
            {{ __('Settings') }}
        </a>
        @if($hasAdminOverride)
            <form method="POST" action="{{ route('settings.role-mode.update') }}">
                @csrf
                <input type="hidden" name="mode" value="admin">
                <button type="submit" class="mobile-more-control">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19 12h2M3 12h2M12 3v2m0 14v2"/></svg></span>
                    {{ __('ui.system_controls') }}
                </button>
            </form>
        @endif
    </div>

    <nav class="mobile-bottom-nav mobile-bottom-nav--student" aria-label="{{ __('Student mobile navigation') }}">
        <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M3 12l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>
            </span>
            <span>{{ __('Home') }}</span>
        </a>
        <a href="{{ route('student.offenses.index') }}" data-liquid-link="fines" class="{{ request()->routeIs('student.offenses.*') ? 'active' : '' }}">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z"/><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/></svg>
            </span>
            <span>{{ __('Fines') }}</span>
        </a>
        <a href="{{ route('student.movements.scan') }}" class="mobile-scan-tab {{ request()->routeIs('student.movements.scan') ? 'active' : '' }}">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 4h6v6H4z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6H4z"/><path d="M14 14h2"/><path d="M18 14h2"/><path d="M14 18h6"/></svg>
            </span>
            <span>{{ __('Scan QR') }}</span>
        </a>
        <a href="{{ route('student.scholarships.index') }}" data-liquid-link="aid" class="{{ $studentOnScholarship ? 'active' : '' }}">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M19 7V6a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v2"/><path d="M3 6v12a2 2 0 0 0 2 2h16v-6h-5a2 2 0 0 1 0-4h5V8"/><path d="M16 14h.01"/></svg>
            </span>
            <span>{{ __('Aid') }}</span>
        </a>
        <button type="button" id="mobileMoreToggle" class="{{ $studentMoreActive ? 'active' : '' }}" aria-expanded="false" aria-controls="mobileMoreSheet">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M5 12h.01"/><path d="M12 12h.01"/><path d="M19 12h.01"/></svg>
            </span>
            <span>{{ __('More') }}</span>
        </button>
    </nav>
@endif

@if($showStaffBottomNav)
    @php
        $staffCategory = $authUser['staff_category'] ?? null;
        $staffWorkRoute = match ($staffCategory) {
            'scholarship' => route('admin.scholarships.index'),
            'discipline' => route('admin.offenses.index'),
            default => route('admin.dashboard'),
        };
        $staffWorkActive = match ($staffCategory) {
            'scholarship' => request()->routeIs('admin.scholarships.*'),
            'discipline' => request()->routeIs('admin.offenses.*'),
            default => false,
        };
    @endphp
    <nav class="mobile-bottom-nav mobile-bottom-nav--staff" aria-label="{{ __('Staff mobile navigation') }}">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="mobile-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 12l9-8 9 8"/><path d="M5 10v10h14V10"/></svg></span><span>{{ __('Home') }}</span>
        </a>
        <a href="{{ $staffWorkRoute }}" class="{{ $staffWorkActive ? 'active' : '' }}">
            <span class="mobile-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 4h14v16H5z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span><span>{{ __('Work') }}</span>
        </a>
        <a href="{{ route('admin.laptops.scan') }}" class="mobile-scan-tab {{ request()->routeIs('admin.laptops.scan*') ? 'active' : '' }}">
            <span class="mobile-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 4h6v6H4z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6H4z"/><path d="M14 14h2M18 14h2M14 18h6"/></svg></span><span>{{ __('Scan QR') }}</span>
        </a>
        @if($lecturerCanManageGuards)
            <a href="{{ route('admin.guards.index') }}" class="{{ request()->routeIs('admin.guards.*') ? 'active' : '' }}">
                <span class="mobile-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4-2.8 7.5-7 9-4.2-1.5-7-5-7-9V6z"/></svg></span><span>{{ __('Guards') }}</span>
            </a>
        @else
            <a href="{{ route('settings.show') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <span class="mobile-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19 12h2M3 12h2M12 3v2m0 14v2"/></svg></span><span>{{ __('Settings') }}</span>
            </a>
        @endif
        <a href="{{ route('admin.profile') }}" class="{{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
            <span class="mobile-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg></span><span>{{ __('Profile') }}</span>
        </a>
    </nav>
@endif

@if($authUser)
<div class="se-notification-center" id="notificationCenter" aria-hidden="true">
    <div class="se-notification-panel" role="dialog" aria-modal="false" aria-labelledby="notificationCenterTitle">
        <div class="se-notification-head">
            <div>
                <span class="se-notification-kicker">MyHEP</span>
                <h2 id="notificationCenterTitle">{{ __('Notifications') }}</h2>
            </div>
            <button type="button" class="se-icon-button" data-notification-close aria-label="{{ __('Close') }}">&times;</button>
        </div>
        <div class="se-notification-list" data-notification-list>
            <div class="se-skeleton-notification"></div>
            <div class="se-skeleton-notification"></div>
            <div class="se-skeleton-notification"></div>
        </div>
    </div>
</div>
@endif

<div class="se-media-modal" id="mediaPreviewModal" aria-hidden="true">
    <div class="se-media-dialog" role="dialog" aria-modal="true" aria-labelledby="mediaPreviewTitle">
        <div class="se-media-toolbar">
            <div>
                <span class="se-media-kicker">{{ __('Preview') }}</span>
                <h2 id="mediaPreviewTitle">{{ __('File preview') }}</h2>
            </div>
            <div class="se-media-actions">
                <a class="se-media-action" data-media-open target="_blank" rel="noopener">{{ __('Open original') }}</a>
                <a class="se-media-action" data-media-download download>{{ __('Download') }}</a>
                <button type="button" class="se-icon-button" data-media-close aria-label="{{ __('Close') }}">&times;</button>
            </div>
        </div>
        <div class="se-media-stage" data-media-stage></div>
    </div>
</div>

<div class="se-page-progress" aria-hidden="true"><span></span></div>

@if($isAdmin && !$isGuardAdmin)
<button type="button" class="se-back-to-top {{ $isLecturerAdmin ? 'is-lecturer' : 'is-admin' }}" id="seBackToTop" aria-label="{{ __('Back to top') }}" title="{{ __('Back to top') }}" aria-hidden="true" tabindex="-1">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 14 6-6 6 6"/></svg>
</button>
@endif

@if($isAdmin && !$isGuardAdmin && !$isLecturerAdmin && $adminAiHelperEnabled && !request()->routeIs('admin.ai-helper.*'))
    @include('partials.admin_ai_chatbox')
@endif

<div class="confirm-modal" id="confirmModal" aria-hidden="true">
    <div class="confirm-dialog" id="confirmDialog" role="dialog" aria-modal="true" aria-labelledby="confirmTitle" aria-describedby="confirmMessage">
        <div class="confirm-head">
            <span class="confirm-icon" aria-hidden="true">!</span>
            <div>
                <span class="confirm-kicker">{{ __('Please confirm') }}</span>
                <h2 class="confirm-title" id="confirmTitle">{{ __('Confirm action') }}</h2>
            </div>
        </div>
        <div class="confirm-body" id="confirmMessage">{{ __('Are you sure you want to continue?') }}</div>
        <div class="confirm-actions">
            <button type="button" class="confirm-btn" id="confirmCancelBtn">{{ __('Cancel') }}</button>
            <button type="button" class="confirm-btn primary" id="confirmProceedBtn">{{ __('Continue') }}</button>
        </div>
    </div>
</div>

@stack('scripts')
<script>
document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-password-toggle]');
    if (!button) return;

    var input = document.getElementById(button.getAttribute('aria-controls'));
    if (!input) return;

    var reveal = input.type === 'password';
    input.type = reveal ? 'text' : 'password';
    button.setAttribute('aria-pressed', reveal ? 'true' : 'false');
    button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
    button.setAttribute('title', reveal ? 'Hide password' : 'Show password');
});
</script>
<script>
(function () {
    var sidebar = document.getElementById('appSidebar');
    var overlay = document.getElementById('sbOverlay');
    var toggle = document.getElementById('sbToggle');
    var closeBtn = document.getElementById('sbClose');
    var hamBox = document.getElementById('hamBox');
    var headerUserBtn = document.getElementById('headerUserBtn');
    var headerUserMenu = document.getElementById('headerUserMenu');
    var headerUserBackdrop = document.getElementById('headerUserBackdrop');
    var headerUserShell = headerUserBtn ? headerUserBtn.closest('.page-header, .topbar') : null;
    var mobileMoreToggle = document.getElementById('mobileMoreToggle');
    var mobileMoreSheet = document.getElementById('mobileMoreSheet');
    var mobileMoreBackdrop = document.getElementById('mobileMoreBackdrop');

    if (headerUserMenu && !headerUserMenu.classList.contains('is-open')) {
        headerUserMenu.setAttribute('aria-hidden', 'true');
    }
    if (sidebar) {
        var dashboardMobileSidebar = document.body.classList.contains('student-dashboard-mobile-sidebar');
        sidebar.setAttribute('aria-hidden', window.innerWidth >= 1024 && !dashboardMobileSidebar ? 'false' : 'true');
    }

    function closeHeaderUserMenu() {
        if (headerUserMenu) {
            headerUserMenu.classList.remove('is-open');
            headerUserMenu.setAttribute('aria-hidden', 'true');
        }
        if (headerUserBackdrop) {
            headerUserBackdrop.classList.remove('is-open');
            headerUserBackdrop.setAttribute('aria-hidden', 'true');
        }
        if (headerUserBtn) headerUserBtn.setAttribute('aria-expanded', 'false');
        if (headerUserShell) headerUserShell.classList.remove('is-user-menu-open');
    }

    function setHeaderUserMenu(open) {
        if (!headerUserMenu || !headerUserBtn) return;
        headerUserMenu.classList.toggle('is-open', open);
        headerUserMenu.setAttribute('aria-hidden', open ? 'false' : 'true');
        headerUserBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (headerUserShell) headerUserShell.classList.toggle('is-user-menu-open', open);
        if (headerUserBackdrop) {
            headerUserBackdrop.classList.toggle('is-open', open);
            headerUserBackdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
    }

    function closeMobileMore() {
        var wasOpen = mobileMoreSheet && mobileMoreSheet.classList.contains('is-open');
        if (mobileMoreSheet) {
            mobileMoreSheet.classList.remove('is-open');
            mobileMoreSheet.setAttribute('aria-hidden', 'true');
        }
        if (mobileMoreBackdrop) {
            mobileMoreBackdrop.classList.remove('is-open');
            mobileMoreBackdrop.setAttribute('aria-hidden', 'true');
        }
        if (mobileMoreToggle) mobileMoreToggle.setAttribute('aria-expanded', 'false');
        if (!document.querySelector('.se-notification-center.is-open, .se-media-modal.is-open, .se-filter-sheet.is-open')) {
            document.body.style.overflow = '';
        }
        if (wasOpen && mobileMoreToggle && document.contains(mobileMoreToggle)) {
            mobileMoreToggle.focus({ preventScroll: true });
        }
    }

    function setMobileMore(open) {
        if (!mobileMoreSheet || !mobileMoreToggle) return;
        mobileMoreSheet.classList.toggle('is-open', open);
        mobileMoreSheet.setAttribute('aria-hidden', open ? 'false' : 'true');
        mobileMoreToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (mobileMoreBackdrop) {
            mobileMoreBackdrop.classList.toggle('is-open', open);
            mobileMoreBackdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
        if (window.innerWidth <= 767) document.body.style.overflow = open ? 'hidden' : '';
        if (open) {
            window.setTimeout(function () {
                var firstAction = mobileMoreSheet.querySelector('a, button');
                if (firstAction) firstAction.focus();
            }, 180);
        }
    }

    function focusSidebarPanel() {
        window.setTimeout(function () {
            if (!sidebar || !sidebar.classList.contains('is-open')) return;
            var firstControl = closeBtn || sidebar.querySelector('button:not([disabled]), a[href], summary, [tabindex]:not([tabindex="-1"])');
            if (firstControl) firstControl.focus({ preventScroll: true });
        }, 180);
    }

    function openSidebar() {
        if (!sidebar) return;
        if (window.innerWidth >= 1024) {
            if (!document.body.classList.contains('student-dashboard-mobile-sidebar')) {
                sidebar.setAttribute('aria-hidden', document.body.classList.contains('student-mobile-shell') ? 'true' : 'false');
                return;
            }
            sidebar.classList.add('is-open');
            sidebar.setAttribute('aria-hidden', 'false');
            document.body.classList.add('sidebar-open');
            if (overlay) overlay.classList.add('is-visible');
            if (hamBox) hamBox.classList.add('is-open-ham');
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
            focusSidebarPanel();
            return;
        }
        sidebar.classList.add('is-open');
        sidebar.setAttribute('aria-hidden', 'false');
        document.body.classList.add('sidebar-open');
        if (overlay) overlay.classList.add('is-visible');
        if (hamBox) hamBox.classList.add('is-open-ham');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        focusSidebarPanel();
    }

    function closeSidebar() {
        if (!sidebar) return;
        var wasOpen = sidebar.classList.contains('is-open');
        if (window.innerWidth >= 1024) {
            if (!document.body.classList.contains('student-dashboard-mobile-sidebar')) {
                sidebar.setAttribute('aria-hidden', document.body.classList.contains('student-mobile-shell') ? 'true' : 'false');
                return;
            }
            sidebar.classList.remove('is-open');
            sidebar.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('sidebar-open');
            if (overlay) overlay.classList.remove('is-visible');
            if (hamBox) hamBox.classList.remove('is-open-ham');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
            if (wasOpen && toggle) toggle.focus({ preventScroll: true });
            return;
        }
        sidebar.classList.remove('is-open');
        sidebar.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('sidebar-open');
        if (overlay) overlay.classList.remove('is-visible');
        if (hamBox) hamBox.classList.remove('is-open-ham');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        if (wasOpen && toggle) toggle.focus({ preventScroll: true });
    }

    if (sidebar && toggle) {
        toggle.addEventListener('click', function () {
            sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
        });
    }
    if (sidebar && closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (sidebar && overlay) overlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Tab' && sidebar && sidebar.classList.contains('is-open')) {
            var focusable = Array.from(sidebar.querySelectorAll('button:not([disabled]), a[href], summary, [tabindex]:not([tabindex="-1"])'))
                .filter(function (element) { return element.offsetParent !== null; });
            if (focusable.length) {
                var first = focusable[0];
                var last = focusable[focusable.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        }
        if (e.key === 'Escape') {
            if (sidebar) closeSidebar();
            closeHeaderUserMenu();
            closeMobileMore();
        }
    });
    if (sidebar) {
        window.addEventListener('resize', function () { if (window.innerWidth >= 1024) closeSidebar(); });
    }

    if (headerUserBtn && headerUserMenu) {
        headerUserBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            setHeaderUserMenu(!headerUserMenu.classList.contains('is-open'));
        });
        document.addEventListener('click', function (e) {
            if (!headerUserMenu.contains(e.target) && !headerUserBtn.contains(e.target)) {
                closeHeaderUserMenu();
            }
        });
        if (headerUserBackdrop) headerUserBackdrop.addEventListener('click', closeHeaderUserMenu);
    }

    document.querySelectorAll('[data-liquid-link]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey
                || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            link.classList.add('is-launching');
        });
    });

    if (mobileMoreToggle && mobileMoreSheet) {
        mobileMoreToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            setMobileMore(!mobileMoreSheet.classList.contains('is-open'));
        });
        if (mobileMoreBackdrop) mobileMoreBackdrop.addEventListener('click', closeMobileMore);
        mobileMoreSheet.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMobileMore);
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) closeMobileMore();
        });
    }

    var confirmModal = document.getElementById('confirmModal');
    var confirmDialog = document.getElementById('confirmDialog');
    var confirmTitle = document.getElementById('confirmTitle');
    var confirmMessage = document.getElementById('confirmMessage');
    var confirmCancelBtn = document.getElementById('confirmCancelBtn');
    var confirmProceedBtn = document.getElementById('confirmProceedBtn');
    var pendingForm = null;
    var pendingSubmitter = null;
    var confirmedForm = null;
    var confirmReturnFocus = null;

    function closeConfirmModal() {
        if (!confirmModal) return;
        confirmModal.classList.remove('is-open');
        confirmModal.setAttribute('aria-hidden', 'true');
        pendingForm = null;
        pendingSubmitter = null;
        if (confirmReturnFocus && document.contains(confirmReturnFocus)) confirmReturnFocus.focus();
        confirmReturnFocus = null;
    }

    function openConfirmModal(form, submitter) {
        if (!confirmModal || !confirmMessage || !confirmProceedBtn) return false;
        pendingForm = form;
        pendingSubmitter = submitter || null;
        confirmReturnFocus = submitter || document.activeElement;
        var message = form.getAttribute('data-confirm-message') || @json(__('Are you sure you want to continue?'));
        var title = form.getAttribute('data-confirm-title') || @json(__('Confirm action'));
        var action = (submitter && submitter.getAttribute('data-confirm-action'))
            || form.getAttribute('data-confirm-action')
            || @json(__('Continue'));
        var tone = (submitter && submitter.getAttribute('data-confirm-tone'))
            || form.getAttribute('data-confirm-tone')
            || 'primary';

        if (confirmTitle) confirmTitle.textContent = title;
        confirmMessage.textContent = message;
        confirmProceedBtn.textContent = action;
        confirmProceedBtn.classList.toggle('danger', tone === 'danger');
        confirmProceedBtn.classList.toggle('primary', tone !== 'danger');
        if (confirmDialog) confirmDialog.dataset.tone = tone;
        confirmModal.classList.add('is-open');
        confirmModal.setAttribute('aria-hidden', 'false');
        confirmCancelBtn && confirmCancelBtn.focus();
        return true;
    }

    document.addEventListener('click', function (event) {
        var submitter = event.target instanceof Element
            ? event.target.closest('button[type="submit"], input[type="submit"]')
            : null;
        if (!submitter || !submitter.form || !submitter.form.hasAttribute('data-confirm-message')) return;
        pendingSubmitter = submitter;
    }, true);

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-message')) return;
        if (confirmedForm === form) {
            confirmedForm = null;
            if (typeof window.studentEdgeSetLoading === 'function') {
                window.studentEdgeSetLoading(form, event.submitter || pendingSubmitter);
            }
            return;
        }
        event.preventDefault();
        openConfirmModal(form, event.submitter || pendingSubmitter);
    }, true);

    if (confirmCancelBtn) confirmCancelBtn.addEventListener('click', closeConfirmModal);
    if (confirmModal) {
        confirmModal.addEventListener('click', function (event) {
            if (event.target === confirmModal) closeConfirmModal();
        });
    }
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && confirmModal && confirmModal.classList.contains('is-open')) {
            closeConfirmModal();
        }
    });
    if (confirmProceedBtn) {
        confirmProceedBtn.addEventListener('click', function () {
            if (!pendingForm) return;
            confirmedForm = pendingForm;
            var form = pendingForm;
            var submitter = pendingSubmitter;
            closeConfirmModal();
            if (submitter) {
                form.requestSubmit(submitter);
                return;
            }
            form.requestSubmit();
        });
    }
})();
</script>
</body>
</html>
