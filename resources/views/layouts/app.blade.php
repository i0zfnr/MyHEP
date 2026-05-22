<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'MyHEP POLIBESUT'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logohep.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logohep.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root {
            --primary: #A48D78;
            --primary-dark: #8a7362;
            --primary-light: #CBB9A4;
            --primary-hover: #f5ede6;
            --accent: #FFDDAB;
            --accent-light: #FAD7BC;
            --sidebar-w: 256px;
            --topbar-h: 56px;
            --surface: #ffffff;
            --bg: #faf7f4;
            --text: #2d1f14;
            --text-muted: #7a6555;
            --text-light: #b09f90;
            --border: #ede4d9;
            --danger: #c0392b;
            --danger-light: #fff0ee;
            --radius: 10px;
            --ease: cubic-bezier(.4,0,.2,1);
            --dur: 270ms;
            --dur-fast: 180ms;
            --dur-slow: 420ms;
            --glass-bg: rgba(255, 255, 255, .58);
            --glass-bg-strong: rgba(255, 255, 255, .76);
            --glass-border: rgba(255, 255, 255, .72);
            --glass-line: rgba(237, 228, 217, .72);
            --glass-shadow: 0 16px 38px rgba(61, 46, 34, .10), inset 0 1px 0 rgba(255,255,255,.72);
            --glass-shadow-hover: 0 22px 48px rgba(61, 46, 34, .14), inset 0 1px 0 rgba(255,255,255,.82);
            --glass-blur: 16px;
        }
        body[data-theme="dark"] {
            --primary: #d7bfa8;
            --primary-dark: #f2dfca;
            --primary-light: #b99b82;
            --primary-hover: rgba(215, 191, 168, .14);
            --accent: #f2c999;
            --accent-light: rgba(242, 201, 153, .18);
            --surface: #171412;
            --bg: #0f0e0d;
            --text: #f7efe8;
            --text-muted: #c8b8a9;
            --text-light: #927f70;
            --border: rgba(226, 209, 192, .16);
            --danger: #fca5a5;
            --danger-light: rgba(127, 29, 29, .24);
            --glass-bg: rgba(24, 21, 18, .68);
            --glass-bg-strong: rgba(24, 21, 18, .84);
            --glass-border: rgba(226, 209, 192, .14);
            --glass-line: rgba(226, 209, 192, .12);
            --glass-shadow: 0 16px 38px rgba(0, 0, 0, .34), inset 0 1px 0 rgba(255,255,255,.04);
            --glass-shadow-hover: 0 22px 48px rgba(0, 0, 0, .42), inset 0 1px 0 rgba(255,255,255,.06);
            color-scheme: dark;
        }
        html, body { margin: 0; padding: 0; min-height: 100vh; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background:
                radial-gradient(circle at 8% 8%, rgba(255, 221, 171, .16), transparent 34%),
                radial-gradient(circle at 92% 6%, rgba(250, 215, 188, .12), transparent 30%),
                var(--bg);
            color: var(--text);
        }
        body[data-theme="dark"] {
            background:
                radial-gradient(circle at 8% 8%, rgba(215, 191, 168, .12), transparent 34%),
                radial-gradient(circle at 92% 6%, rgba(242, 201, 153, .08), transparent 30%),
                var(--bg);
        }
        body[data-theme="dark"] input,
        body[data-theme="dark"] select,
        body[data-theme="dark"] textarea {
            background: rgba(24, 21, 18, .82);
            border-color: var(--border);
            color: var(--text);
        }
        body[data-theme="dark"] input::placeholder,
        body[data-theme="dark"] textarea::placeholder {
            color: var(--text-light);
        }
        body[data-theme="dark"] .page-body .card,
        body[data-theme="dark"] .page-body .stat,
        body[data-theme="dark"] .page-body .sch-card,
        body[data-theme="dark"] .page-body .ann-item,
        body[data-theme="dark"] .page-body .rule-row,
        body[data-theme="dark"] .page-body .portal-card,
        body[data-theme="dark"] .page-body .data-card,
        body[data-theme="dark"] .page-body .monitor-card,
        body[data-theme="dark"] .page-body .monitor-kpi {
            background: var(--surface);
            border-color: var(--border);
            color: var(--text);
        }
        body[data-theme="dark"] .page-body h1,
        body[data-theme="dark"] .page-body h2,
        body[data-theme="dark"] .page-body h3,
        body[data-theme="dark"] .page-body .title,
        body[data-theme="dark"] .page-body .stat-value,
        body[data-theme="dark"] .page-body .stat .value {
            color: var(--text) !important;
        }
        body[data-theme="dark"] .page-body p,
        body[data-theme="dark"] .page-body label,
        body[data-theme="dark"] .page-body small,
        body[data-theme="dark"] .page-body .muted,
        body[data-theme="dark"] .page-body .hint,
        body[data-theme="dark"] .page-body .cat {
            color: var(--text-muted);
        }
        body[data-theme="dark"] .page-body table,
        body[data-theme="dark"] .page-body th,
        body[data-theme="dark"] .page-body td {
            border-color: var(--border);
            color: var(--text);
        }
        body[data-theme="dark"] .page-body th {
            background: rgba(215, 191, 168, .10);
        }
        body[data-theme="dark"] .page-body .btn:not(.btn-primary):not(.btn-danger),
        body[data-theme="dark"] .page-body .ann-link {
            background: var(--surface);
            border-color: var(--border);
            color: var(--primary-dark);
        }
        body[data-theme="dark"] .sidebar {
            background: rgba(18, 16, 14, .94);
            border-right-color: var(--glass-line);
        }
        body[data-theme="dark"] .sb-header,
        body[data-theme="dark"] .sb-footer,
        body[data-theme="dark"] .app-footer {
            background: rgba(18, 16, 14, .90);
            border-color: var(--glass-line);
            color: var(--text-muted);
        }
        body[data-theme="dark"] .page-header {
            background:
                linear-gradient(180deg, rgba(18, 16, 14, .88), rgba(18, 16, 14, .72)),
                radial-gradient(circle at 96% 0%, rgba(215,191,168,.10), transparent 34%),
                radial-gradient(circle at 10% 0%, rgba(95,190,145,.07), transparent 28%);
            border-color: rgba(226, 209, 192, .12);
            box-shadow:
                0 12px 30px rgba(0,0,0,.24),
                inset 0 1px 0 rgba(255,255,255,.055);
            color: var(--text-muted);
        }
        body[data-theme="dark"] .page-header::after {
            background: linear-gradient(90deg, transparent, rgba(215,191,168,.22), rgba(95,190,145,.14), transparent);
        }
        body[data-theme="dark"] .sb-brand-icon {
            background: rgba(255, 255, 255, .92);
            border-color: rgba(255, 255, 255, .14);
        }
        body[data-theme="dark"] .sb-user {
            background: linear-gradient(135deg, rgba(44, 37, 31, .96), rgba(30, 26, 22, .92));
            border-color: var(--glass-border);
            box-shadow: 0 12px 24px rgba(0, 0, 0, .26);
        }
        body[data-theme="dark"] .nav-link {
            color: var(--text-muted);
        }
        body[data-theme="dark"] .nav-link:hover,
        body[data-theme="dark"] .nav-link.active,
        body[data-theme="dark"] .nav-group[open] > .nav-group-toggle,
        body[data-theme="dark"] .nav-group:hover > .nav-group-toggle {
            background: rgba(215, 191, 168, .16);
            color: var(--text);
        }
        body[data-theme="dark"] .nav-icon,
        body[data-theme="dark"] .nav-chevron {
            color: var(--primary);
        }
        body[data-theme="dark"] .btn-logout {
            background: rgba(127, 29, 29, .12);
            border-color: rgba(252, 165, 165, .55);
            color: #fecaca;
        }
        body[data-theme="dark"] .btn-logout:hover {
            background: rgba(127, 29, 29, .24);
        }
        body[data-theme="dark"] .page-header h1,
        body[data-theme="dark"] .page-header h2,
        body[data-theme="dark"] .page-header h3,
        body[data-theme="dark"] .page-header [style*="color:#2d1f14"],
        body[data-theme="dark"] .page-header [style*="color: #2d1f14"],
        body[data-theme="dark"] .page-header [style*="color:#241a12"],
        body[data-theme="dark"] .page-header [style*="color: #241a12"] {
            color: var(--text) !important;
        }
        body[data-theme="dark"] .header-support,
        body[data-theme="dark"] .header-user,
        body[data-theme="dark"] .header-user-menu {
            background: rgba(255,255,255,.075);
            border-color: rgba(226, 209, 192, .16);
            color: var(--text);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.075);
        }
        body[data-theme="dark"] .header-support {
            color: var(--text-muted);
        }
        body[data-theme="dark"] .header-user-menu {
            background:
                linear-gradient(180deg, rgba(42, 37, 32, .92), rgba(25, 22, 19, .88)),
                radial-gradient(circle at 100% 0%, rgba(215,191,168,.14), transparent 42%) !important;
            border-color: rgba(226, 209, 192, .20);
            box-shadow:
                0 20px 46px rgba(0,0,0,.42),
                inset 0 1px 0 rgba(255,255,255,.10),
                inset 0 0 0 1px rgba(255,255,255,.035);
            backdrop-filter: blur(30px) saturate(150%);
            -webkit-backdrop-filter: blur(30px) saturate(150%);
        }
        body[data-theme="dark"] .header-user-menu::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: rgba(10, 9, 8, .20);
            pointer-events: none;
        }
        body[data-theme="dark"] .header-user-menu > * {
            position: relative;
            z-index: 1;
        }
        body[data-theme="dark"] .header-menu-name,
        body[data-theme="dark"] .header-menu-link,
        body[data-theme="dark"] .header-menu-btn {
            color: #fff7ef;
            text-shadow: 0 1px 1px rgba(0,0,0,.28);
        }
        body[data-theme="dark"] .header-user-avatar {
            background: linear-gradient(135deg, #b99b82, #ead5bd);
            color: #17110d;
            box-shadow: 0 6px 14px rgba(0,0,0,.24), inset 0 1px 0 rgba(255,255,255,.24);
        }
        body[data-theme="dark"] .header-user-role,
        body[data-theme="dark"] .header-menu-role {
            color: var(--text-light);
        }
        body[data-theme="dark"] .header-support:hover,
        body[data-theme="dark"] .header-user:hover,
        body[data-theme="dark"] .header-menu-link:hover,
        body[data-theme="dark"] .header-menu-btn:hover {
            background: rgba(215,191,168,.14);
            border-color: rgba(215,191,168,.34);
            color: var(--text);
        }
        body[data-theme="dark"] .header-menu-head,
        body[data-theme="dark"] .header-menu-sep {
            border-color: rgba(226, 209, 192, .12);
        }
        .app-layout { display: flex; min-height: 100vh; align-items: stretch; }

        .ui-shell { max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 1rem; }
        .ui-hero {
            position: relative;
            border-radius: 18px;
            background: linear-gradient(135deg, #3d2e22 0%, #6e5440 50%, #8a6d52 100%);
            color: #fff;
            padding: 1.5rem 1.75rem;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .22);
            box-shadow: var(--glass-shadow);
        }
        .ui-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 85% 20%, rgba(201,168,76,.2) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 10% 90%, rgba(138,109,82,.33) 0%, transparent 60%);
            pointer-events: none;
        }
        .ui-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(115deg, rgba(255, 255, 255, .14), rgba(255, 255, 255, .04) 40%, transparent 70%);
            pointer-events: none;
        }
        .ui-hero > * { position: relative; z-index: 1; }
        .ui-hero h3 { margin: 0 0 .35rem; font-size: 1.5rem; line-height: 1.2; }
        .ui-hero p { margin: 0; color: rgba(255,255,255,.82); font-size: .9rem; }

        .ui-section-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--text-light);
            margin: 0 0 .5rem;
            padding-left: 2px;
        }

        .ui-card,
        .page-body .card {
            background:
                linear-gradient(145deg, rgba(255,255,255,.76), rgba(255,250,245,.50)),
                radial-gradient(circle at 94% 8%, rgba(164,141,120,.12), transparent 34%);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--glass-shadow);
            backdrop-filter: blur(var(--glass-blur)) saturate(136%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(136%);
        }
        .ui-card-head {
            padding: .9rem 1rem;
            border-bottom: 1px solid var(--glass-line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
            background: linear-gradient(180deg, rgba(255,255,255,.60), rgba(255,255,255,.24));
        }
        .ui-card-body { padding: 1rem; }

        .ui-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }
        @media (min-width: 700px) { .ui-stats-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .ui-stat-card {
            background:
                linear-gradient(145deg, rgba(255,255,255,.74), rgba(255,250,245,.48)),
                radial-gradient(circle at 94% 12%, rgba(164,141,120,.10), transparent 34%);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 1rem;
            box-shadow: var(--glass-shadow);
            backdrop-filter: blur(var(--glass-blur)) saturate(136%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(136%);
        }
        .ui-stat-label { font-size: .72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .ui-stat-value { font-size: 1.65rem; font-weight: 700; color: var(--text); line-height: 1.15; margin-top: .2rem; }

        .ui-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
        .ui-btn,
        .page-body .btn,
        .page-body .btn-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid #cbb9a4;
            background: #fff;
            color: #8a7362;
            border-radius: 9px;
            padding: .5rem .85rem;
            text-decoration: none;
            font-weight: 600;
            font-size: .82rem;
            line-height: 1.2;
            cursor: pointer;
        }
        .ui-btn:hover,
        .page-body .btn:hover,
        .page-body .btn-link:hover {
            background: var(--primary-hover);
            border-color: var(--primary-light);
            color: var(--primary-dark);
        }
        .ui-btn.primary,
        .page-body .btn-primary {
            border-color: var(--primary-dark);
            background: var(--primary-dark);
            color: #fff;
        }
        .ui-btn.primary:hover,
        .page-body .btn-primary:hover {
            background: #6e5a4c;
            border-color: #6e5a4c;
            color: #fff;
        }

        .ui-table,
        .page-body table {
            width: 100%;
            border-collapse: collapse;
        }
        .ui-table th, .ui-table td,
        .page-body th, .page-body td {
            padding: .68rem .9rem;
            font-size: .82rem;
            border-bottom: 1px solid #f0e7dc;
            text-align: left;
        }
        .ui-table th,
        .page-body th {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            background: #faf7f4;
        }

        .ui-status,
        .page-body .status-badge {
            display: inline-block;
            padding: .2rem .55rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid #ede4d9;
            background: #faf7f4;
            color: #7a6555;
        }
        .page-body .status-unpaid,
        .page-body .status-rejected { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
        .page-body .status-applied,
        .page-body .status-pending { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
        .page-body .status-paid,
        .page-body .status-approved,
        .page-body .status-confirmed { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }

        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w); height: 100%; min-height: 100vh;
            z-index: 200; display: flex; flex-direction: column;
            background: rgba(255,255,255,.84); border-right: 1px solid var(--glass-line);
            backdrop-filter: blur(var(--glass-blur)) saturate(130%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(130%);
            transform: translateX(-100%); transition: transform var(--dur) var(--ease), box-shadow var(--dur) var(--ease);
            overflow: visible;
        }
        .sidebar.is-open { transform: translateX(0); box-shadow: 8px 0 40px rgba(164,141,120,.2); }
        @media (min-width: 1024px) {
            .sidebar { position: sticky; top: 0; height: 100vh; min-height: 100vh; transform: translateX(0) !important; box-shadow: none !important; }
        }

        .sb-header { display: flex; align-items: center; justify-content: space-between; height: var(--topbar-h); padding: 0 1rem; border-bottom: 1px solid var(--border); flex-shrink: 0; }
        .sb-brand { display: flex; align-items: center; gap: .625rem; text-decoration: none; }
        .sb-brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #fff;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .sb-brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 3px;
        }
        .sb-brand-name { font-size: .8125rem; font-weight: 700; color: var(--text); line-height: 1.2; }
        .sb-brand-sub { font-size: .6rem; font-weight: 600; color: var(--text-muted); letter-spacing: .06em; text-transform: uppercase; }
        .sb-close { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border: none; background: none; border-radius: 7px; color: var(--text-muted); cursor: pointer; }
        .sb-close:hover { background: var(--bg); color: var(--text); }
        .sb-close svg { width: 14px; height: 14px; }
        @media (min-width: 1024px) { .sb-close { display: none !important; } }

        .sb-user {
            margin: .875rem .875rem .375rem;
            padding: .75rem .875rem;
            background: linear-gradient(135deg, rgba(253,244,237,.96), rgba(250,240,232,.88));
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(61, 46, 34, .06);
            backdrop-filter: blur(calc(var(--glass-blur) * .55)) saturate(120%);
            -webkit-backdrop-filter: blur(calc(var(--glass-blur) * .55)) saturate(120%);
        }
        .sb-user-row { display: flex; align-items: center; gap: .625rem; }
        .sb-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; color: #fff; }
        .sb-user-name { font-size: .8125rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sb-user-role { font-size: .7rem; color: var(--text-muted); margin-top: 1px; }
        .sb-role-badge { display: inline-flex; align-items: center; margin-top: .5rem; padding: .2rem .65rem; border-radius: 99px; font-size: .65rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .sb-role-badge.student { background: #edfaf4; color: #166534; border: 1px solid #bbf7d0; }
        .sb-role-badge.admin { background: var(--primary-hover); color: var(--primary-dark); border: 1px solid var(--primary-light); }

        .sb-scroll { flex: 1; overflow-y: auto; overflow-x: hidden; padding: .625rem .875rem; min-height: 0; }
        .nav-label { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-light); padding: .2rem .5rem .45rem; margin-top: .875rem; }
        .nav-label:first-child { margin-top: 0; }
        .nav-link { display: flex; align-items: center; gap: .625rem; padding: .575rem .625rem; border-radius: 8px; font-size: .8125rem; font-weight: 500; color: var(--text-muted); text-decoration: none; margin-bottom: 2px; position: relative; transition: background-color var(--dur-fast) var(--ease), color var(--dur-fast) var(--ease), transform var(--dur-fast) var(--ease), box-shadow var(--dur-fast) var(--ease); }
        .nav-link:hover { background: var(--primary-hover); color: var(--primary-dark); transform: translateX(2px); }
        .nav-link.active { background: var(--primary-hover); color: var(--primary-dark); font-weight: 700; }
        .nav-link.active::before { content: ''; position: absolute; left: 0; top: 18%; bottom: 18%; width: 3px; background: var(--primary); border-radius: 0 3px 3px 0; }
        .nav-icon { width: 15px; height: 15px; color: var(--primary-light); }
        .nav-group { margin-bottom: 2px; }
        .nav-group summary { list-style: none; }
        .nav-group summary::-webkit-details-marker { display: none; }
        .nav-group-toggle {
            width: 100%;
            border: 0;
            cursor: pointer;
            font-family: inherit;
            user-select: none;
        }
        .nav-group-toggle .nav-chevron {
            width: 13px;
            height: 13px;
            margin-left: auto;
            color: var(--text-light);
            transition: transform var(--dur-fast) var(--ease), color var(--dur-fast) var(--ease);
        }
        .nav-group[open] > .nav-group-toggle,
        .nav-group:hover > .nav-group-toggle {
            background: var(--primary-hover);
            color: var(--primary-dark);
            font-weight: 700;
        }
        .nav-group[open] > .nav-group-toggle .nav-chevron,
        .nav-group:hover > .nav-group-toggle .nav-chevron {
            transform: rotate(180deg);
            color: var(--primary-dark);
        }
        .nav-group.active > .nav-group-toggle::before {
            content: '';
            position: absolute;
            left: 0;
            top: 18%;
            bottom: 18%;
            width: 3px;
            background: var(--primary);
            border-radius: 0 3px 3px 0;
        }
        .nav-submenu {
            display: none;
            margin: .15rem 0 .45rem;
            padding-left: .45rem;
            border-left: 1px solid var(--border);
        }
        .nav-group[open] > .nav-submenu,
        .nav-group:hover > .nav-submenu,
        .nav-group:focus-within > .nav-submenu {
            display: block;
        }
        .nav-submenu .nav-link {
            margin-left: .35rem;
            padding-top: .5rem;
            padding-bottom: .5rem;
            font-size: .78rem;
        }

        .sb-footer { padding: .75rem .875rem; border-top: 1px solid var(--glass-line); flex-shrink: 0; background: rgba(255,255,255,.68); }
        .btn-logout { display: flex; align-items: center; gap: .5rem; width: 100%; padding: .55rem .75rem; border-radius: 8px; border: 1px solid #fecaca; background: none; font-size: .8125rem; font-weight: 600; color: var(--danger); cursor: pointer; }
        .btn-logout:hover { background: var(--danger-light); border-color: #fca5a5; }
        .btn-logout svg { width: 14px; height: 14px; }

        .sb-overlay { display: none; position: fixed; inset: 0; z-index: 150; background: rgba(45,31,20,.45); opacity: 0; pointer-events: none; transition: opacity var(--dur) var(--ease); }
        .sb-overlay.is-visible { opacity: 1; pointer-events: auto; }
        @media (max-width: 1023px) { .sb-overlay { display: block; } }

        .main-wrap { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .topbar {
            display: flex;
            align-items: center;
            gap: .75rem;
            height: var(--topbar-h);
            padding: 0 1rem;
            background: var(--glass-bg-strong);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(var(--glass-blur)) saturate(125%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(125%);
        }
        @media (min-width: 1024px) { .topbar { display: none; } }
        .topbar-title { font-size: .875rem; font-weight: 700; color: var(--text); flex: 1; }
        .btn-ham { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid var(--border); border-radius: 8px; background: none; cursor: pointer; color: var(--text-muted); }
        .btn-ham:hover { background: var(--primary-hover); color: var(--primary-dark); }
        .ham-box { display: flex; flex-direction: column; gap: 4px; }
        .ham-line { display: block; width: 17px; height: 2px; background: currentColor; border-radius: 2px; }
        .is-open-ham .ham-line:nth-child(1) { transform: translateY(6px) rotate(45deg); }
        .is-open-ham .ham-line:nth-child(2) { opacity: 0; }
        .is-open-ham .ham-line:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }

        .page-header {
            position: sticky;
            top: 0;
            z-index: 90;
            background:
                linear-gradient(180deg, rgba(255,255,255,.82), rgba(255,255,255,.64)),
                radial-gradient(circle at 100% 0%, rgba(203,185,164,.18), transparent 34%);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 10px 28px rgba(61, 46, 34, .08), inset 0 1px 0 rgba(255,255,255,.72);
            padding: 1rem 1.25rem;
            backdrop-filter: blur(var(--glass-blur)) saturate(125%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(125%);
        }
        .page-header::after {
            content: '';
            position: absolute;
            left: 1.25rem;
            right: 1.25rem;
            bottom: -1px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(164,141,120,.32), transparent);
            pointer-events: none;
        }
        @media (min-width: 640px) { .page-header { padding: 1.125rem 1.5rem; } }
        @media (min-width: 1024px) {
            .page-header { padding: 1.05rem 2rem; }
            .page-header::after { left: 2rem; right: 2rem; }
        }
        .page-header-inner { display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; }
        .page-header-left {
            min-width: 0;
            display: flex;
            align-items: center;
        }
        .page-header-left h1,
        .page-header-left h2,
        .page-header-left h3 {
            margin: 0 !important;
            font-size: clamp(1rem, 1.5vw, 1.15rem) !important;
            line-height: 1.25 !important;
            font-weight: 800 !important;
            letter-spacing: 0 !important;
            color: var(--text) !important;
        }
        .page-header-right { display: flex; align-items: center; gap: .55rem; margin-left: auto; position: relative; }
        .header-support {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 1px solid var(--glass-border);
            border-radius: 999px;
            padding: .45rem .7rem;
            font-size: .78rem;
            color: var(--text-muted);
            text-decoration: none;
            background: rgba(255, 255, 255, .82);
            font-weight: 600;
        }
        .header-support:hover { border-color: var(--primary-light); color: var(--primary-dark); background: var(--primary-hover); }
        .header-user {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border: 1px solid var(--glass-border);
            border-radius: 999px;
            padding: .28rem .6rem .28rem .28rem;
            text-decoration: none;
            color: var(--text);
            background: rgba(255, 255, 255, .82);
            min-width: 0;
            cursor: pointer;
        }
        .header-user:hover { border-color: var(--primary-light); background: var(--primary-hover); }
        .header-user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .68rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .header-user-meta { min-width: 0; }
        .header-user-name {
            font-size: .78rem;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
            line-height: 1.15;
        }
        .header-user-role {
            font-size: .66rem;
            color: var(--text-muted);
            line-height: 1.1;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .header-user-menu {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            min-width: 230px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,250,245,.84));
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            box-shadow:
                0 18px 38px rgba(45,31,20,.18),
                inset 0 1px 0 rgba(255,255,255,.76);
            backdrop-filter: blur(24px) saturate(145%);
            -webkit-backdrop-filter: blur(24px) saturate(145%);
            padding: .45rem;
            z-index: 50;
            display: none;
        }
        .header-user-menu.is-open { display: block; }
        .header-menu-head {
            padding: .45rem .55rem .6rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: .35rem;
        }
        .header-menu-name { font-size: .82rem; font-weight: 700; color: var(--text); }
        .header-menu-role { font-size: .68rem; color: var(--text-muted); text-transform: uppercase; margin-top: 2px; }
        .header-menu-link, .header-menu-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: .45rem;
            padding: .5rem .55rem;
            border-radius: 8px;
            font-size: .8rem;
            color: var(--text);
            text-decoration: none;
            border: none;
            background: transparent;
            text-align: left;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
        }
        .header-menu-link:hover, .header-menu-btn:hover {
            background: var(--primary-hover);
            color: var(--primary-dark);
        }
        .header-menu-sep { border-top: 1px solid var(--border); margin: .35rem 0; }
        .header-menu-btn.logout { color: var(--danger); }
        .header-menu-btn.logout:hover { background: var(--danger-light); color: var(--danger); }
        .page-body { flex: 1; padding: 1.25rem 1rem; }
        @media (min-width: 640px) { .page-body { padding: 1.5rem; } }
        @media (min-width: 1024px) { .page-body { padding: 2rem; } }
        .app-footer {
            border-top: 1px solid var(--border);
            padding: .85rem 1rem;
            font-size: .78rem;
            color: var(--text-muted);
            text-align: center;
            background: rgba(255, 255, 255, .9);
            backdrop-filter: blur(calc(var(--glass-blur) * .5));
            -webkit-backdrop-filter: blur(calc(var(--glass-blur) * .5));
        }
        @media (min-width: 1024px) { .app-footer { padding: .95rem 2rem; } }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes softPop {
            from { opacity: 0; transform: scale(.985); }
            to { opacity: 1; transform: scale(1); }
        }

        .page-header {
            animation: fadeSlideIn var(--dur-slow) var(--ease) both;
        }
        .page-body > * {
            animation: fadeSlideIn var(--dur-slow) var(--ease) both;
        }
        .page-body > *:nth-child(1) { animation-delay: 30ms; }
        .page-body > *:nth-child(2) { animation-delay: 80ms; }
        .page-body > *:nth-child(3) { animation-delay: 130ms; }
        .page-body > *:nth-child(4) { animation-delay: 180ms; }

        .page-body .card,
        .page-body .portal-card,
        .page-body .stat-card,
        .page-body .data-card,
        .page-body .monitor-card,
        .page-body .monitor-kpi,
        .page-body .no-access,
        .page-body .settings-card,
        .page-body table {
            animation: softPop var(--dur-slow) var(--ease) both;
        }

        .page-body .card,
        .page-body .portal-card,
        .page-body .stat-card,
        .page-body .data-card,
        .page-body .monitor-card,
        .page-body .monitor-kpi,
        .page-body .no-access,
        .page-body .settings-card,
        .page-body .filters,
        .page-body .filter-card,
        .page-body .form-card,
        .page-body .panel,
        .page-body .table-wrap,
        .page-body form.card {
            background:
                linear-gradient(145deg, rgba(255,255,255,.76), rgba(255,250,245,.50)),
                radial-gradient(circle at 96% 10%, rgba(164,141,120,.10), transparent 35%) !important;
            border-color: var(--glass-border) !important;
            box-shadow: var(--glass-shadow) !important;
            backdrop-filter: blur(var(--glass-blur)) saturate(136%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(136%);
        }

        .page-body .card h2,
        .page-body .card h3,
        .page-body .data-card-head,
        .page-body .ui-card-head,
        .page-body .filters,
        .page-body thead th {
            background: linear-gradient(180deg, rgba(255,255,255,.66), rgba(255,255,255,.28)) !important;
            border-color: var(--glass-line) !important;
        }

        .page-body input,
        .page-body select,
        .page-body textarea {
            background: rgba(255,255,255,.64) !important;
            border-color: rgba(203,185,164,.72) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .page-body .btn:not(.btn-primary),
        .page-body .btn-link,
        .page-body .portal-link,
        .page-body .action-btn,
        .page-body .btn-ghost {
            background: rgba(255,255,255,.58) !important;
            border-color: rgba(203,185,164,.66) !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.68);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .page-body a,
        .page-body button,
        .page-body .btn,
        .page-body .btn-link,
        .page-body input,
        .page-body select,
        .page-body textarea {
            transition: background-color var(--dur-fast) var(--ease), color var(--dur-fast) var(--ease), border-color var(--dur-fast) var(--ease), box-shadow var(--dur-fast) var(--ease), transform var(--dur-fast) var(--ease), opacity var(--dur-fast) var(--ease);
        }

        .page-body .btn:hover,
        .page-body .btn-link:hover,
        .page-body button:hover {
            transform: translateY(-1px);
        }
        .page-body .card:hover,
        .page-body .portal-card:hover,
        .page-body .stat-card:hover,
        .page-body .data-card:hover,
        .page-body .monitor-card:hover,
        .page-body .monitor-kpi:hover {
            border-color: rgba(203,185,164,.78) !important;
            box-shadow: var(--glass-shadow-hover) !important;
        }

        body[data-theme="dark"] .page-body {
            background:
                radial-gradient(circle at 12% 4%, rgba(215, 191, 168, .09), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(95, 132, 113, .08), transparent 30%),
                linear-gradient(180deg, rgba(255,255,255,.015), transparent 18%);
        }
        body[data-theme="dark"] .page-body .card,
        body[data-theme="dark"] .page-body .portal-card,
        body[data-theme="dark"] .page-body .stat-card,
        body[data-theme="dark"] .page-body .data-card,
        body[data-theme="dark"] .page-body .monitor-card,
        body[data-theme="dark"] .page-body .monitor-kpi,
        body[data-theme="dark"] .page-body .no-access,
        body[data-theme="dark"] .page-body .settings-card,
        body[data-theme="dark"] .page-body .filters,
        body[data-theme="dark"] .page-body .filter-card,
        body[data-theme="dark"] .page-body .form-card,
        body[data-theme="dark"] .page-body .panel,
        body[data-theme="dark"] .page-body .table-wrap,
        body[data-theme="dark"] .page-body form.card {
            background:
                linear-gradient(145deg, rgba(32, 28, 24, .82), rgba(15, 14, 12, .72)),
                radial-gradient(circle at 8% 0%, rgba(255,255,255,.065), transparent 34%),
                radial-gradient(circle at 100% 0%, rgba(215,191,168,.055), transparent 40%) !important;
            border-color: rgba(226, 209, 192, .15) !important;
            box-shadow:
                0 18px 42px rgba(0, 0, 0, .28),
                inset 0 1px 0 rgba(255,255,255,.07),
                inset 0 -1px 0 rgba(0,0,0,.22) !important;
            color: var(--text);
            backdrop-filter: blur(18px) saturate(126%);
            -webkit-backdrop-filter: blur(18px) saturate(126%);
        }
        body[data-theme="dark"] .page-body .dash-hero {
            background:
                linear-gradient(135deg, rgba(38, 33, 29, .92), rgba(18, 16, 14, .82)),
                radial-gradient(circle at 10% 0%, rgba(255,255,255,.075), transparent 36%),
                radial-gradient(circle at 100% 50%, rgba(215,191,168,.07), transparent 42%) !important;
        }
        body[data-theme="dark"] .page-body .dash-hero::before {
            background: linear-gradient(135deg, transparent 0%, rgba(215,191,168,.10) 100%) !important;
        }
        body[data-theme="dark"] .page-body .card h2,
        body[data-theme="dark"] .page-body .card h3,
        body[data-theme="dark"] .page-body .data-card-head,
        body[data-theme="dark"] .page-body .ui-card-head,
        body[data-theme="dark"] .page-body .filters,
        body[data-theme="dark"] .page-body thead th {
            background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.025)) !important;
            border-color: rgba(226, 209, 192, .14) !important;
        }
        body[data-theme="dark"] .page-body input,
        body[data-theme="dark"] .page-body select,
        body[data-theme="dark"] .page-body textarea {
            background: rgba(10, 9, 8, .72) !important;
            border-color: rgba(226, 209, 192, .22) !important;
            color: var(--text) !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.04);
        }
        body[data-theme="dark"] .page-body .btn:not(.btn-primary),
        body[data-theme="dark"] .page-body .btn-link,
        body[data-theme="dark"] .page-body .portal-link,
        body[data-theme="dark"] .page-body .action-btn,
        body[data-theme="dark"] .page-body .btn-ghost {
            background: rgba(255,255,255,.075) !important;
            border-color: rgba(226, 209, 192, .18) !important;
            color: var(--text) !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
        }
        body[data-theme="dark"] .page-body .portal-link:hover,
        body[data-theme="dark"] .page-body .btn:not(.btn-primary):hover {
            background: rgba(215,191,168,.16) !important;
            border-color: rgba(215,191,168,.42) !important;
        }
        body[data-theme="dark"] .page-body .monitor-pill,
        body[data-theme="dark"] .page-body .dash-hero-label,
        body[data-theme="dark"] .page-body .status-badge,
        body[data-theme="dark"] .page-body .status {
            box-shadow: inset 0 1px 0 rgba(255,255,255,.18);
        }
        body[data-theme="dark"] .page-body .card:hover,
        body[data-theme="dark"] .page-body .portal-card:hover,
        body[data-theme="dark"] .page-body .stat-card:hover,
        body[data-theme="dark"] .page-body .data-card:hover,
        body[data-theme="dark"] .page-body .monitor-card:hover,
        body[data-theme="dark"] .page-body .monitor-kpi:hover {
            border-color: rgba(215,191,168,.30) !important;
            box-shadow:
                0 24px 52px rgba(0, 0, 0, .36),
                inset 0 1px 0 rgba(255,255,255,.10) !important;
        }
        body[data-theme="dark"] .page-body .head,
        body[data-theme="dark"] .page-body .card h2,
        body[data-theme="dark"] .page-body .card-head,
        body[data-theme="dark"] .page-body .maint-card-head,
        body[data-theme="dark"] .page-body .section-head,
        body[data-theme="dark"] .page-body .panel-head {
            background:
                linear-gradient(180deg, rgba(255,255,255,.075), rgba(255,255,255,.025)) !important;
            border-color: rgba(226, 209, 192, .14) !important;
            color: var(--text) !important;
        }
        body[data-theme="dark"] .page-body .head h1,
        body[data-theme="dark"] .page-body .head h2,
        body[data-theme="dark"] .page-body .head h3,
        body[data-theme="dark"] .page-body .card h2,
        body[data-theme="dark"] .page-body .card-head *,
        body[data-theme="dark"] .page-body .maint-card-head {
            color: var(--text) !important;
        }
        body[data-theme="dark"] .page-body .head::before,
        body[data-theme="dark"] .page-body .card h2::before {
            background: linear-gradient(180deg, #d7bfa8 0%, #5fbe91 100%) !important;
        }
        body[data-theme="dark"] .page-body .stat,
        body[data-theme="dark"] .page-body .summary,
        body[data-theme="dark"] .page-body .summary-card,
        body[data-theme="dark"] .page-body .maint-hero,
        body[data-theme="dark"] .page-body .maint-card,
        body[data-theme="dark"] .page-body .camera-panel,
        body[data-theme="dark"] .page-body .camera-box,
        body[data-theme="dark"] .page-body .upload-box,
        body[data-theme="dark"] .page-body .rules-list,
        body[data-theme="dark"] .page-body .rule-row {
            background:
                linear-gradient(145deg, rgba(30, 26, 22, .82), rgba(12, 11, 10, .70)),
                radial-gradient(circle at 8% 0%, rgba(255,255,255,.055), transparent 36%) !important;
            border-color: rgba(226, 209, 192, .16) !important;
            color: var(--text) !important;
            box-shadow:
                0 16px 34px rgba(0, 0, 0, .24),
                inset 0 1px 0 rgba(255,255,255,.06) !important;
        }
        body[data-theme="dark"] .page-body .rules-list {
            padding: .45rem;
            scrollbar-color: rgba(215,191,168,.45) rgba(255,255,255,.06);
        }
        body[data-theme="dark"] .page-body .rule-top,
        body[data-theme="dark"] .page-body .rule-row label,
        body[data-theme="dark"] .page-body .rules-selected-only,
        body[data-theme="dark"] .page-body .rules-selected-count,
        body[data-theme="dark"] .page-body .camera-msg,
        body[data-theme="dark"] .page-body .help,
        body[data-theme="dark"] .page-body .hint,
        body[data-theme="dark"] .page-body .maint-key,
        body[data-theme="dark"] .page-body .maint-hero p {
            color: var(--text-muted) !important;
        }
        body[data-theme="dark"] .page-body .maint-hero h3,
        body[data-theme="dark"] .page-body .maint-val,
        body[data-theme="dark"] .page-body .rule-title,
        body[data-theme="dark"] .page-body .rule-row strong {
            color: var(--text) !important;
        }
        body[data-theme="dark"] .page-body .maint-row {
            border-color: rgba(226, 209, 192, .14) !important;
        }
        body[data-theme="dark"] .page-body .maint-note,
        body[data-theme="dark"] .page-body .camera-msg.err,
        body[data-theme="dark"] .page-body .error {
            background: rgba(127, 29, 29, .22) !important;
            border-color: rgba(252, 165, 165, .28) !important;
            color: #fecaca !important;
        }
        body[data-theme="dark"] .page-body .maint-url {
            background: rgba(10, 9, 8, .62) !important;
            border-color: rgba(226, 209, 192, .20) !important;
            color: var(--primary-dark) !important;
        }
        body[data-theme="dark"] .page-body input[type="file"] {
            padding: .55rem !important;
        }
        body[data-theme="dark"] .page-body input[type="file"]::file-selector-button {
            margin-right: .75rem;
            border: 1px solid rgba(226, 209, 192, .22);
            border-radius: 8px;
            background: rgba(255,255,255,.085);
            color: var(--text);
            padding: .45rem .7rem;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        body[data-theme="dark"] .page-body table {
            background: rgba(14, 13, 12, .72) !important;
            border-color: rgba(226, 209, 192, .14) !important;
        }
        body[data-theme="dark"] .page-body tbody tr {
            background: rgba(12, 11, 10, .34) !important;
        }
        body[data-theme="dark"] .page-body tbody tr:hover {
            background: rgba(215, 191, 168, .075) !important;
        }
        body[data-theme="dark"] .page-body td,
        body[data-theme="dark"] .page-body th {
            border-color: rgba(226, 209, 192, .13) !important;
        }
        body[data-theme="dark"] .page-body [style*="color:#2d1f14"],
        body[data-theme="dark"] .page-body [style*="color: #2d1f14"],
        body[data-theme="dark"] .page-body [style*="color:#241a12"],
        body[data-theme="dark"] .page-body [style*="color: #241a12"] {
            color: var(--text) !important;
        }
        body[data-theme="dark"] .page-body .btn-primary,
        body[data-theme="dark"] .page-body .maint-btn.ok,
        body[data-theme="dark"] .page-body button.btn-primary {
            background: linear-gradient(135deg, #b99b82 0%, #e4cdb7 100%) !important;
            border-color: rgba(255,255,255,.08) !important;
            color: #15110e !important;
            box-shadow: 0 10px 22px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.25) !important;
        }
        body[data-theme="dark"] .page-body .maint-btn.warn {
            background: rgba(127, 29, 29, .22) !important;
            border-color: rgba(252, 165, 165, .32) !important;
            color: #fecaca !important;
        }
        body[data-theme="dark"] .page-body .badge,
        body[data-theme="dark"] .page-body .status {
            border-color: rgba(255,255,255,.16) !important;
        }
        body[data-theme="dark"] .page-body .miya-page {
            background:
                radial-gradient(780px 360px at 50% -12%, rgba(104, 151, 130, .22) 0%, rgba(32, 43, 37, .16) 48%, transparent 76%),
                linear-gradient(145deg, rgba(33, 31, 28, .82), rgba(14, 13, 12, .72)) !important;
            border-color: rgba(226, 209, 192, .18) !important;
            box-shadow: 0 24px 58px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.08) !important;
        }
        body[data-theme="dark"] .page-body .miya-page::before {
            background:
                linear-gradient(120deg, rgba(255,255,255,.09), transparent 42%),
                radial-gradient(circle at 86% 18%, rgba(95, 190, 145, .12), transparent 32%) !important;
        }
        body[data-theme="dark"] .page-body .miya-clock,
        body[data-theme="dark"] .page-body .miya-close,
        body[data-theme="dark"] .page-body .miya-title,
        body[data-theme="dark"] .page-body .miya-chip {
            color: var(--text) !important;
        }
        body[data-theme="dark"] .page-body .miya-logo,
        body[data-theme="dark"] .page-body .miya-chip,
        body[data-theme="dark"] .page-body .miya-input-wrap,
        body[data-theme="dark"] .page-body .miya-close,
        body[data-theme="dark"] .page-body .miya-mic {
            background: rgba(255,255,255,.075) !important;
            border-color: rgba(226, 209, 192, .18) !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08) !important;
        }
        body[data-theme="dark"] .page-body .miya-input {
            background: transparent !important;
            color: var(--text) !important;
        }
        body[data-theme="dark"] .page-body .miya-terms,
        body[data-theme="dark"] .page-body .miya-note {
            color: var(--text-muted) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 1ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 1ms !important;
                scroll-behavior: auto !important;
            }
        }

        @media (max-width: 768px) {
            :root { --glass-blur: 8px; }
            .page-header,
            .topbar {
                box-shadow: 0 4px 16px rgba(61, 46, 34, .06);
            }
            .header-user-name {
                max-width: 130px;
            }
        }

        @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
            .sb-user,
            .topbar,
            .page-header,
            .header-user-menu,
            .app-footer,
            .page-body .card,
            .page-body .portal-card,
            .page-body .stat-card,
            .page-body .data-card,
            .page-body .monitor-card,
            .page-body .monitor-kpi {
                background: #fff;
            }
        }
    </style>
</head>
<body data-theme="{{ session('theme', 'light') }}">
@php
    $authUser = session('auth_user');
    $isStudent = ($authUser['role'] ?? null) === 'student';
    $isAdmin = ($authUser['role'] ?? null) === 'admin';
    $adminScope = $authUser['admin_role'] ?? null;
    $isScholarshipAdmin = $isAdmin && in_array($adminScope, ['scholarship_admin', 'system_admin'], true);
    $isDisciplineAdmin = $isAdmin && in_array($adminScope, ['discipline_admin', 'system_admin'], true);
    $studentOnDashboard = request()->routeIs('student.dashboard');
    $adminOnDashboard = request()->routeIs('admin.dashboard');
    $studentOnScholarship = request()->routeIs('student.scholarships.*')
        || request()->routeIs('student.scholarship-status.*');
    $studentOnDiscipline = request()->routeIs('student.offenses.*')
        || request()->routeIs('student.rules.*')
        || request()->routeIs('student.vehicle-stickers.*')
        || request()->routeIs('student.discipline-announcements.*');
    $adminOnDiscipline = request()->routeIs('admin.students.*')
        || request()->routeIs('admin.offenses.*')
        || request()->routeIs('admin.fine-applications.*')
        || request()->routeIs('admin.vehicle-stickers.*')
        || request()->routeIs('admin.discipline-announcements.*')
        || request()->routeIs('admin.rules.*');
    $adminOnScholarship = request()->routeIs('admin.scholarships.*')
        || request()->routeIs('admin.student-scholarship-status.*')
        || request()->routeIs('admin.scholarship-announcements.*');
    $showSidebar = (bool) $authUser && !($isStudent && $studentOnDashboard);
    $showHeaderUserMenu = (bool) $authUser && ($studentOnDashboard || $adminOnDashboard);
@endphp
<div class="app-layout">

    @if($showSidebar)
    <aside class="sidebar" id="appSidebar" role="navigation" aria-label="{{ __('Navigasi utama') }}">
        <div class="sb-header">
            <a href="{{ route('home') }}" class="sb-brand">
                <div class="sb-brand-icon">
                    <img src="{{ asset('images/logohep.png') }}" alt="Logo MyHEP POLIBESUT">
                </div>
                <div><div class="sb-brand-name">MyHEP</div><div class="sb-brand-sub">POLIBESUT</div></div>
            </a>
            <button class="sb-close" id="sbClose" aria-label="{{ __('Tutup sidebar') }}"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <div class="sb-user">
            <div class="sb-user-row">
                <div class="sb-avatar">{{ strtoupper(substr($authUser['name'] ?? 'U', 0, 2)) }}</div>
                <div style="min-width:0">
                    <div class="sb-user-name">{{ $authUser['name'] ?? __('Pengguna') }}</div>
                    <div class="sb-user-role">{{ $authUser['role'] ?? '-' }}{{ $isAdmin && $adminScope ? ' - '.$adminScope : '' }}</div>
                </div>
            </div>
            @if($isStudent)
                <span class="sb-role-badge student">{{ __('Pelajar') }}</span>
            @elseif($isAdmin)
                <span class="sb-role-badge admin">{{ __('Admin') }}</span>
            @endif
        </div>

        <div class="sb-scroll">
            @if($isStudent)
                <div class="nav-label">{{ __('ui.main_menu') }}</div>
                <nav>
                    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2 7-7 7 7"/></svg>
                        {{ __('Index') }}
                    </a>
                    <a href="{{ route('student.ai-helper.index') }}" class="nav-link {{ request()->routeIs('student.ai-helper.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-2.846.813a1.125 1.125 0 000 2.124L9 22.5l.813 2.846a1.125 1.125 0 002.124 0L12.75 22.5l2.846-.813a1.125 1.125 0 000-2.124L12.75 18.75l-.813-2.846a1.125 1.125 0 00-2.124 0zM18.75 8.25l-.433 1.517L16.8 10.2a.75.75 0 000 1.44l1.517.433.433 1.517a.75.75 0 001.44 0l.433-1.517 1.517-.433a.75.75 0 000-1.44l-1.517-.433-.433-1.517a.75.75 0 00-1.44 0zM2.25 4.5l.433 1.517L4.2 6.45a.75.75 0 010 1.44l-1.517.433L2.25 9.84a.75.75 0 01-1.44 0L.377 8.323-1.14 7.89a.75.75 0 010-1.44l1.517-.433L.81 4.5a.75.75 0 011.44 0z"/></svg>
                        {{ __('AI Helper') }}
                    </a>
                </nav>

                @if($studentOnScholarship)
                    <div class="nav-label">{{ __('Scholarship') }}</div>
                    <nav>
                        <a href="{{ route('student.scholarships.index') }}" class="nav-link {{ request()->routeIs('student.scholarships.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                            {{ __('Scholarship') }}
                        </a>
                        <a href="{{ route('student.scholarship-status.form') }}" class="nav-link {{ request()->routeIs('student.scholarship-status.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-7.5A2.25 2.25 0 014.5 17.25V6.75A2.25 2.25 0 016.75 4.5h7.5A2.25 2.25 0 0116.5 6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.5M8.25 12.75h4.5"/></svg>
                            {{ __('Borang Status Biasiswa') }}
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
                                <a href="{{ route('student.scholarship-status.form') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-7.5A2.25 2.25 0 014.5 17.25V6.75A2.25 2.25 0 016.75 4.5h7.5A2.25 2.25 0 0116.5 6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.5M8.25 12.75h4.5"/></svg>
                                    {{ __('Status Form') }}
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
                                <a href="{{ route('student.rules.index') }}" class="nav-link">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75H7.5A2.25 2.25 0 005.25 9v9A2.25 2.25 0 007.5 20.25h9A2.25 2.25 0 0018.75 18V9A2.25 2.25 0 0016.5 6.75H12z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 11.25h6M9 14.25h6"/></svg>
                                    {{ __('Rules') }}
                                </a>
                            </div>
                        </details>
                    </nav>
                @endif

                <div class="nav-label">{{ __('ui.sidebar_account') }}</div>
                <nav>
                    <a href="{{ route('student.profile') }}" class="nav-link {{ request()->routeIs('student.profile*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        {{ __('Profil') }}
                    </a>
                    <a href="{{ route('settings.show') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v2.25l1.5 1.5"/></svg>
                        {{ __('ui.settings') }}
                    </a>
                </nav>
            @elseif($isAdmin)
                <div class="nav-label">{{ __('Papan Pemuka') }}</div>
                <nav>
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2 7-7 7 7"/></svg>
                        {{ __('ui.dashboard') }}
                    </a>
                    <a href="{{ route('admin.reports.monthly') }}" class="nav-link {{ request()->routeIs('admin.reports.monthly') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v18h16.5M7.5 15l3-3 2.25 2.25L16.5 9"/></svg>
                        {{ __('ui.monthly_report') }}
                    </a>
                    <a href="{{ route('admin.ai-helper.index') }}" class="nav-link {{ request()->routeIs('admin.ai-helper.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-2.846.813a1.125 1.125 0 000 2.124L9 22.5l.813 2.846a1.125 1.125 0 002.124 0L12.75 22.5l2.846-.813a1.125 1.125 0 000-2.124L12.75 18.75l-.813-2.846a1.125 1.125 0 00-2.124 0zM18.75 8.25l-.433 1.517L16.8 10.2a.75.75 0 000 1.44l1.517.433.433 1.517a.75.75 0 001.44 0l.433-1.517 1.517-.433a.75.75 0 000-1.44l-1.517-.433-.433-1.517a.75.75 0 00-1.44 0zM2.25 4.5l.433 1.517L4.2 6.45a.75.75 0 010 1.44l-1.517.433L2.25 9.84a.75.75 0 01-1.44 0L.377 8.323-1.14 7.89a.75.75 0 010-1.44l1.517-.433L.81 4.5a.75.75 0 011.44 0z"/></svg>
                        {{ __('AI Helper') }}
                    </a>
                </nav>

                @if($isScholarshipAdmin)
                    <nav>
                        @if($adminScope === 'system_admin')
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
                                <a href="{{ route('admin.scholarships.index') }}" class="nav-link {{ request()->routeIs('admin.scholarships.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                    {{ __('Rekod Scholarship') }}
                                </a>
                                <a href="{{ route('admin.student-scholarship-status.index') }}" class="nav-link {{ request()->routeIs('admin.student-scholarship-status.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-7.5A2.25 2.25 0 014.5 17.25V6.75A2.25 2.25 0 016.75 4.5h7.5A2.25 2.25 0 0116.5 6.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.5M8.25 12.75h4.5"/></svg>
                                    {{ __('Data Status Biasiswa') }}
                                </a>
                                <a href="{{ route('admin.scholarships.create') }}" class="nav-link {{ request()->routeIs('admin.scholarships.create') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    {{ __('Tambah Rekod') }}
                                </a>
                                <a href="{{ route('admin.scholarship-announcements.index') }}" class="nav-link {{ request()->routeIs('admin.scholarship-announcements.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h9m-9 3h5.25M3.75 6.75A2.25 2.25 0 016 4.5h12A2.25 2.25 0 0120.25 6.75v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z"/></svg>
                                    {{ __('Pengumuman') }}
                                </a>
                                <a href="{{ route('admin.scholarship-announcements.create') }}" class="nav-link {{ request()->routeIs('admin.scholarship-announcements.create') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    {{ __('Tambah Pengumuman') }}
                                </a>
                            </div>
                        @if($adminScope === 'system_admin')
                            </details>
                        @endif
                    </nav>
                @endif

                @if($isDisciplineAdmin)
                    <nav>
                        @if($adminScope === 'system_admin')
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
                                <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                                    {{ __('Pelajar') }}
                                </a>
                                <a href="{{ route('admin.offenses.index') }}" class="nav-link {{ request()->routeIs('admin.offenses.index') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6"/></svg>
                                    {{ __('Senarai Kesalahan') }}
                                </a>
                                <a href="{{ route('admin.offenses.create') }}" class="nav-link {{ request()->routeIs('admin.offenses.create') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    {{ __('Daftar Kesalahan') }}
                                </a>
                                <a href="{{ route('admin.fine-applications.index') }}" class="nav-link {{ request()->routeIs('admin.fine-applications.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5"/></svg>
                                    {{ __('Permohonan Bayaran') }}
                                </a>
                                <a href="{{ route('admin.vehicle-stickers.index') }}" class="nav-link {{ request()->routeIs('admin.vehicle-stickers.*') ? 'active' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l1.5-4.5a2.25 2.25 0 012.136-1.54h9.228A2.25 2.25 0 0118.75 9l1.5 4.5M5.25 13.5h13.5M6 16.5h.75m10.5 0H18m-12 0a.75.75 0 00-.75.75v.75c0 .414.336.75.75.75h.75a.75.75 0 00.75-.75v-.75a.75.75 0 00-.75-.75H6zm10.5 0a.75.75 0 00-.75.75v.75c0 .414.336.75.75.75h.75a.75.75 0 00.75-.75v-.75a.75.75 0 00-.75-.75h-.75z"/></svg>
                                    {{ __('Sticker Kenderaan') }}
                                </a>
                                <a href="{{ route('admin.discipline-announcements.index') }}" class="nav-link {{ request()->routeIs('admin.discipline-announcements.*') ? 'active' : '' }}">
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
                        @if($adminScope === 'system_admin')
                            </details>
                        @endif
                    </nav>
                @endif

                @if($adminScope === 'system_admin')
                    <div class="nav-label">{{ __('Sistem') }}</div>
                    <nav>
                        <a href="{{ route('admin.maintenance.index') }}" class="nav-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.83-5.83M11.42 15.17l2.496-3.03a3.375 3.375 0 00-4.773-4.773L6.113 9.864m5.307 5.307L9.864 6.113m0 0L4.5 3.75 3.75 4.5l2.363 5.364m3.751-3.751L15.17 11.42"/></svg>
                            {{ __('Maintenance') }}
                        </a>
                        <a href="{{ route('admin.admin-users.index') }}" class="nav-link {{ request()->routeIs('admin.admin-users.*') ? 'active' : '' }}">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.742-1.34 9.04 9.04 0 00-2.983-3.163m-1.358 5.663A9.035 9.035 0 0112 21a9.035 9.035 0 01-5.401-1.68m10.802 0a9.035 9.035 0 00-10.802 0M6.599 19.32a9.04 9.04 0 01-2.983-3.16A9.095 9.095 0 007.358 14.82m11.384-.44a9.05 9.05 0 00-15.484 0m15.484 0A9.03 9.03 0 0012 12c-2.305 0-4.41.867-6 2.38m12.742 0A9.03 9.03 0 0112 12m0 0a3 3 0 100-6 3 3 0 000 6z"/></svg>
                            {{ __('Pengurusan Admin') }}
                        </a>
                    </nav>
                @endif
                <div class="nav-label">{{ __('ui.sidebar_account') }}</div>
                <nav>
                    <a href="{{ route('settings.show') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v2.25l1.5 1.5"/></svg>
                        {{ __('ui.settings') }}
                    </a>
                </nav>
            @endif
        </div>

        <div class="sb-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                    {{ __('ui.logout') }}
                </button>
            </form>
        </div>
    </aside>

    <div class="sb-overlay" id="sbOverlay" aria-hidden="true"></div>
    @endif

    <div class="main-wrap">
        @if($showSidebar)
        <div class="topbar">
            <button class="btn-ham" id="sbToggle" aria-label="{{ __('Buka sidebar') }}" aria-expanded="false" aria-controls="appSidebar">
                <div class="ham-box" id="hamBox"><span class="ham-line"></span><span class="ham-line"></span><span class="ham-line"></span></div>
            </button>
            <span class="topbar-title">MyHEP POLIBESUT</span>
        </div>
        @endif

        @hasSection('header')
            <div class="page-header">
                <div class="page-header-inner">
                    <div class="page-header-left">@yield('header')</div>
                    @if($showHeaderUserMenu)
                        <div class="page-header-right">
                            <a href="mailto:support@polibesut.edu.my?subject=MyHEP%20Support" class="header-support">
                                {{ __('Support') }}
                            </a>
                            <button type="button" class="header-user" id="headerUserBtn" aria-expanded="false" aria-haspopup="menu">
                                <span class="header-user-avatar">{{ strtoupper(substr($authUser['name'] ?? 'U', 0, 2)) }}</span>
                                <span class="header-user-meta">
                                    <span class="header-user-name">{{ $authUser['name'] ?? __('User') }}</span>
                                    <span class="header-user-role">{{ $authUser['role'] ?? '-' }}</span>
                                </span>
                            </button>
                            <div class="header-user-menu" id="headerUserMenu" role="menu" aria-label="{{ __('User menu') }}">
                                <div class="header-menu-head">
                                    <div class="header-menu-name">{{ $authUser['name'] ?? __('User') }}</div>
                                    <div class="header-menu-role">{{ $authUser['role'] ?? '-' }}</div>
                                </div>
                                <a href="{{ route('settings.show') }}" class="header-menu-link">{{ __('Settings') }}</a>
                                <a href="mailto:support@polibesut.edu.my?subject=MyHEP%20Support" class="header-menu-link">{{ __('Support') }}</a>
                                <div class="header-menu-sep"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="header-menu-btn logout">{{ __('Log Out') }}</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <main class="page-body">@yield('content')</main>
        @include('partials.app_footer')
    </div>
</div>

@stack('scripts')
<script>
(function () {
    var sidebar = document.getElementById('appSidebar');
    var overlay = document.getElementById('sbOverlay');
    var toggle = document.getElementById('sbToggle');
    var closeBtn = document.getElementById('sbClose');
    var hamBox = document.getElementById('hamBox');
    var headerUserBtn = document.getElementById('headerUserBtn');
    var headerUserMenu = document.getElementById('headerUserMenu');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('is-open');
        if (overlay) overlay.classList.add('is-visible');
        if (hamBox) hamBox.classList.add('is-open-ham');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('is-open');
        if (overlay) overlay.classList.remove('is-visible');
        if (hamBox) hamBox.classList.remove('is-open-ham');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (sidebar && toggle) {
        toggle.addEventListener('click', function () {
            sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
        });
    }
    if (sidebar && closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (sidebar && overlay) overlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (sidebar) closeSidebar();
            if (headerUserMenu) headerUserMenu.classList.remove('is-open');
            if (headerUserBtn) headerUserBtn.setAttribute('aria-expanded', 'false');
        }
    });
    if (sidebar) {
        window.addEventListener('resize', function () { if (window.innerWidth >= 1024) closeSidebar(); });
    }

    if (headerUserBtn && headerUserMenu) {
        headerUserBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = headerUserMenu.classList.toggle('is-open');
            headerUserBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
            if (!headerUserMenu.contains(e.target) && !headerUserBtn.contains(e.target)) {
                headerUserMenu.classList.remove('is-open');
                headerUserBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }
})();
</script>
</body>
</html>

