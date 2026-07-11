<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('bug_reports.public_title') }} - StudentEdge</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logohep.png') }}">
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
            --ok-border: #86efac;
            --ok-text: #166534;
        }

        * { box-sizing: border-box; }
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
        }
        .panel,
        .info-card {
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
                width: min(100% - 20px, 1120px);
                padding-top: 20px;
                padding-bottom: 32px;
            }
            .topbar {
                flex-direction: column;
                align-items: stretch;
            }
            .grid {
                grid-template-columns: 1fr;
            }
            .panel-head,
            .panel-body,
            .info-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="topbar">
            <a href="{{ route('home') }}" class="brand">
                <img src="{{ asset('images/logohep.png') }}" alt="StudentEdge">
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
                                <input id="reporter_name" name="reporter_name" type="text" value="{{ old('reporter_name') }}" required>
                            </div>
                            <div class="field">
                                <label for="reporter_email">{{ __('bug_reports.form_email') }}</label>
                                <input id="reporter_email" name="reporter_email" type="email" value="{{ old('reporter_email') }}" required>
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
                            <div class="field">
                                <label for="page_url">{{ __('bug_reports.form_page_url') }}</label>
                                <input id="page_url" name="page_url" type="url" value="{{ old('page_url', url()->previous() !== url()->current() ? url()->previous() : '') }}" placeholder="https://example.com/page">
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
