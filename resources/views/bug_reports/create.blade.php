<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.theme_bootstrap')
    <meta name="theme-color" content="#171412">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ __('bug_reports.public_title') }} - StudentEdge</title>
    @include('partials.brand_icons')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg: #f6f1ea;
            --surface: rgba(255, 255, 255, 0.86);
            --surface-strong: #ffffff;
            --border: rgba(194, 168, 143, 0.36);
            --text: #261b14;
            --muted: #766353;
            --accent: #b69b82;
            --accent-dark: #8e7158;
            --ring: rgba(182, 155, 130, 0.2);
            --danger-bg: #fff1f2;
            --danger-border: #fda4af;
            --danger-text: #b42318;
            --ok-bg: #ecfdf3;
            --ok-border: #85c2c5;
            --ok-text: #1f5559;
        }

        * { box-sizing: border-box; }
        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Plus Jakarta Sans", "Inter", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(230, 214, 197, 0.9), transparent 34%),
                radial-gradient(circle at right center, rgba(215, 191, 168, 0.55), transparent 28%),
                linear-gradient(180deg, #faf7f2 0%, var(--bg) 100%);
        }
        .shell {
            width: min(1120px, calc(100% - 32px));
            min-width: 0;
            margin: 0 auto;
            padding: 36px 0 56px;
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            text-decoration: none;
            font-weight: 800;
        }
        .brand img {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid rgba(194, 168, 143, 0.45);
            background: rgba(255,255,255,.8);
            padding: 6px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.72);
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
        }
        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
            gap: 24px;
            align-items: stretch;
            min-width: 0;
        }
        .panel,
        .info-card {
            min-width: 0;
            border: 1px solid var(--border);
            border-radius: 28px;
            background: var(--surface);
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 54px rgba(61, 46, 34, 0.08);
        }
        .panel {
            overflow: hidden;
        }
        .panel-head {
            padding: 28px 28px 20px;
            border-bottom: 1px solid rgba(194, 168, 143, 0.22);
            background:
                radial-gradient(circle at top right, rgba(215, 191, 168, 0.18), transparent 34%),
                linear-gradient(180deg, rgba(255,255,255,.6), rgba(255,255,255,.28));
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(182, 155, 130, 0.14);
            color: var(--accent-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        h1 {
            margin: 14px 0 10px;
            font-size: clamp(1.9rem, 4vw, 2.8rem);
            line-height: 1.05;
            letter-spacing: -.04em;
        }
        .lead {
            margin: 0;
            max-width: 700px;
            color: var(--muted);
            line-height: 1.75;
            font-size: 1rem;
            overflow-wrap: anywhere;
        }
        .panel-body {
            padding: 28px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        .field {
            display: grid;
            min-width: 0;
            gap: 10px;
        }
        .field.full {
            grid-column: 1 / -1;
        }
        label {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #6f5d50;
        }
        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--surface-strong);
            color: var(--text);
            padding: 15px 16px;
            font: inherit;
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px var(--ring);
        }
        textarea {
            min-height: 170px;
            resize: vertical;
        }
        .hint {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px 18px;
            text-decoration: none;
            font-weight: 800;
            cursor: pointer;
        }
        .btn-primary {
            border-color: #9f846e;
            background: linear-gradient(135deg, var(--accent) 0%, #d9c1a8 100%);
            color: #fff;
            box-shadow: 0 12px 28px rgba(182, 155, 130, 0.28);
        }
        .btn-secondary {
            background: rgba(255,255,255,.62);
            color: var(--text);
        }
        body[data-theme="dark"] .btn-primary {
            border-color: rgba(240,211,154,.38) !important;
            background: linear-gradient(135deg, #f0d39a 0%, #c28a3d 100%) !important;
            color: #25190f !important;
            box-shadow: 0 12px 28px rgba(194,138,61,.22);
        }
        body[data-theme="dark"] .btn-secondary {
            background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(215,191,168,.04)) !important;
            border-color: rgba(226,209,192,.26) !important;
            color: #f7efe8 !important;
        }
        .message {
            margin-bottom: 18px;
            border-radius: 18px;
            padding: 14px 16px;
            font-weight: 700;
            line-height: 1.6;
        }
        .message.ok {
            background: var(--ok-bg);
            border: 1px solid var(--ok-border);
            color: var(--ok-text);
        }
        .message.err {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
        }
        .info-card {
            padding: 24px;
            display: grid;
            gap: 18px;
            align-content: start;
        }
        .info-block {
            border-radius: 22px;
            border: 1px solid rgba(194, 168, 143, 0.2);
            background: rgba(255,255,255,.54);
            padding: 18px;
        }
        .info-block h2 {
            margin: 0 0 8px;
            font-size: 1rem;
        }
        .info-block p,
        .info-block li {
            color: var(--muted);
            line-height: 1.7;
        }
        .info-block ul {
            margin: 0;
            padding-left: 18px;
        }
        @media (max-width: 920px) {
            .hero {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 640px) {
            .shell {
                width: min(calc(100% - 20px), 1120px);
                padding-top: 20px;
                padding-bottom: 32px;
            }
            .topbar {
                gap: 10px;
                margin-bottom: 18px;
            }
            .brand {
                min-width: 0;
            }
            .brand img {
                width: 38px;
                height: 38px;
            }
            .back-link {
                flex: 0 0 auto;
                padding: 10px 13px;
                white-space: nowrap;
            }
            .grid {
                grid-template-columns: 1fr;
            }
            .panel-head,
            .panel-body,
            .info-card {
                padding: 18px;
            }
            h1 {
                font-size: clamp(1.65rem, 8vw, 2rem);
            }
            .lead {
                font-size: .92rem;
                line-height: 1.65;
            }
        }

        /* StudentEdge support-page alignment */
        .hero { grid-template-columns: minmax(0, 1.35fr) minmax(300px, .65fr); align-items: start; }
        .panel,
        .info-card { border-radius: 20px; border-color: var(--border); box-shadow: 0 18px 46px rgba(61, 46, 34, .10); }
        .panel-head {
            padding: 26px 28px;
            border-bottom-color: rgba(255,255,255,.14);
            background: linear-gradient(135deg, #3b291d 0%, #765237 58%, #a77950 100%);
        }
        .panel-head h1 { color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,.18); }
        .panel-head .lead { color: rgba(255,255,255,.82); }
        .panel-head .eyebrow { color: #f8e2c8; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.14); }
        .panel-body { background: var(--surface); }
        .field { gap: 7px; }
        .field label { color: var(--text); font-size: .72rem; letter-spacing: .07em; }
        input,
        select,
        textarea { min-height: 48px; border-radius: 12px; background: color-mix(in srgb, var(--surface-strong) 94%, transparent); }
        select { color-scheme: light; cursor: pointer; }
        select option { background: #fff; color: #261b14; }
        textarea { min-height: 158px; }
        .btn { min-height: 46px; border-radius: 12px; }
        .btn-primary { background: var(--accent-dark); border-color: var(--accent-dark); color: #fff; }
        .info-card { padding: 16px; gap: 12px; background: var(--surface); }
        .info-block { padding: 17px; border-radius: 15px; background: color-mix(in srgb, var(--surface-strong) 82%, var(--accent) 18%); }
        .info-block h2 { color: var(--text); }
        .info-block p,
        .info-block li { margin-top: 0; color: var(--muted); }
        .info-block:last-child { border-color: color-mix(in srgb, var(--border) 60%, var(--accent) 40%); }
        body[data-theme="dark"] .panel,
        body[data-theme="dark"] .info-card { background: rgba(22,19,17,.88); border-color: rgba(226,209,192,.22); }
        body[data-theme="dark"] .panel-body { background: rgba(18,16,14,.92); }
        body[data-theme="dark"] .field label,
        body[data-theme="dark"] .info-block h2 { color: #f7efe8; }
        body[data-theme="dark"] input,
        body[data-theme="dark"] select,
        body[data-theme="dark"] textarea { background: rgba(10,9,8,.72); border-color: rgba(226,209,192,.25); color: #f7efe8; }
        body[data-theme="dark"] select { color-scheme: dark; }
        body[data-theme="dark"] select option { background: #171412; color: #f7efe8; }
        body[data-theme="dark"] .info-block { background: rgba(255,255,255,.045); border-color: rgba(226,209,192,.18); }
        body[data-theme="dark"] .info-block p,
        body[data-theme="dark"] .info-block li,
        body[data-theme="dark"] .hint { color: #cdbbaa; }
        @media (max-width: 920px) { .hero { grid-template-columns: 1fr; } }
        @media (max-width: 640px) {
            .panel-head,
            .panel-body { padding: 20px; }
            .info-card { padding: 12px; }
            .actions .btn { flex: 1 1 140px; }
        }
    </style>
    @vite('resources/css/design-system.css')
</head>
<body data-theme="{{ session('theme', 'light') }}">
@include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--standalone'])
    <div class="shell">
        <div class="topbar">
            <a href="{{ route('home') }}" class="brand">
                <img src="{{ asset('images/studentedge-mark.png') }}?v=11" alt="StudentEdge">
                <span>StudentEdge</span>
            </a>
            <a href="{{ route('home') }}" class="back-link">{{ __('bug_reports.back_home') }}</a>
        </div>

        <div class="hero">
            <section class="panel">
                <div class="panel-head">
                    <span class="eyebrow">{{ __('bug_reports.public_eyebrow') }}</span>
                    <h1>{{ __('bug_reports.public_heading') }}</h1>
                    <p class="lead">{{ __('bug_reports.public_description') }}</p>
                </div>

                <div class="panel-body">
                    @if(session('success'))
                        <div class="message ok">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="message err">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bug-reports.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid">
                            <div class="field">
                                <label for="reporter_name">{{ __('bug_reports.form_name') }}</label>
                                <input id="reporter_name" name="reporter_name" type="text" value="{{ old('reporter_name', $authenticatedReporter?->full_name) }}" required @if($authenticatedReporter) readonly @endif>
                            </div>
                            <div class="field">
                                <label for="reporter_email">{{ __('bug_reports.form_email') }}</label>
                                <input id="reporter_email" name="reporter_email" type="email" value="{{ old('reporter_email', $authenticatedReporter?->email) }}" required @if($authenticatedReporter?->email) readonly @endif>
                            </div>
                            <div class="field">
                                <label for="category">{{ __('bug_reports.form_category') }}</label>
                                <select id="category" name="category" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(old('category', 'bug') === $category)>
                                            {{ __('bug_reports.category_' . $category) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field full">
                                <label for="subject">{{ __('bug_reports.form_subject') }}</label>
                                <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required>
                            </div>
                            <div class="field full">
                                <label for="description">{{ __('bug_reports.form_description') }}</label>
                                <textarea id="description" name="description" required>{{ old('description') }}</textarea>
                                <div class="hint">{{ __('bug_reports.form_description_hint') }}</div>
                            </div>
                            <div class="field full">
                                <label for="screenshot">{{ __('bug_reports.form_screenshot') }}</label>
                                <input id="screenshot" name="screenshot" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <div class="hint">{{ __('bug_reports.form_screenshot_hint') }}</div>
                            </div>
                        </div>

                        <div class="actions">
                            <button type="submit" class="btn btn-primary">{{ __('bug_reports.form_submit') }}</button>
                            <a href="{{ route('home') }}" class="btn btn-secondary">{{ __('bug_reports.form_cancel') }}</a>
                        </div>
                    </form>
                </div>
            </section>

            <aside class="info-card">
                <div class="info-block">
                    <h2>{{ __('bug_reports.help_title') }}</h2>
                    <ul>
                        <li>{{ __('bug_reports.help_point_1') }}</li>
                        <li>{{ __('bug_reports.help_point_2') }}</li>
                        <li>{{ __('bug_reports.help_point_3') }}</li>
                    </ul>
                </div>
                <div class="info-block">
                    <h2>{{ __('bug_reports.privacy_title') }}</h2>
                    <p>{{ __('bug_reports.privacy_description') }}</p>
                </div>
                <div class="info-block">
                    <h2>{{ __('bug_reports.response_title') }}</h2>
                    <p>{{ __('bug_reports.response_description') }}</p>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
