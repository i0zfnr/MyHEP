<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="welcome-document">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.theme_bootstrap')
    <meta name="theme-color" content="#171412">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ __('home.page_title') }}</title>
    @include('partials.brand_icons')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">


    @vite('resources/css/design-system.css')
</head>
<body data-theme="{{ session('theme', 'light') }}" class="welcome-page">
<div class="pwa-launch" id="pwaLaunch" aria-hidden="true">
    <div class="pwa-launch-content">
        <div class="pwa-launch-mark"><img src="{{ asset('images/myhep-mark.png') }}?v=11" alt=""></div>
        <strong class="pwa-launch-title">MyHEP</strong>
        <p class="pwa-launch-copy">Politeknik Besut</p>
        <span class="pwa-launch-progress" aria-hidden="true"></span>
    </div>
</div>
<script>
    (() => {
        const standalone = ['standalone', 'fullscreen', 'minimal-ui', 'window-controls-overlay']
            .some((mode) => window.matchMedia(`(display-mode: ${mode})`).matches)
            || window.navigator.standalone === true
            || document.referrer.startsWith('android-app://');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const launch = document.getElementById('pwaLaunch');

        if (!standalone || reduceMotion || !launch) return;
        try {
            if (window.sessionStorage.getItem('myhep-pwa-launch-seen') === '1') return;
            window.sessionStorage.setItem('myhep-pwa-launch-seen', '1');
        } catch (_) {
            // The launch transition remains safe when storage is unavailable.
        }

        launch.classList.add('is-visible');
        window.setTimeout(() => launch.classList.add('is-leaving'), 820);
        window.setTimeout(() => launch.remove(), 1120);
    })();
</script>
@include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--standalone'])
<script>
    (() => {
        let deferredPrompt = null;
        const displayModeQueries = [
            '(display-mode: standalone)',
            '(display-mode: fullscreen)',
            '(display-mode: minimal-ui)',
            '(display-mode: window-controls-overlay)',
        ];
        const isInstalledDisplayMode = () =>
            displayModeQueries.some((query) => window.matchMedia(query).matches)
            || window.navigator.standalone === true
            || document.referrer.startsWith('android-app://');
        const isIosSafari = () => {
            const userAgent = window.navigator.userAgent;
            const isIos = /iPad|iPhone|iPod/.test(userAgent)
                || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);

            return isIos && /Safari/.test(userAgent) && !/CriOS|FxiOS|EdgiOS|OPiOS/.test(userAgent);
        };
        const syncInstallOptions = () => {
            const options = document.querySelector('.pwa-options');
            const androidButton = document.getElementById('androidInstallButton');
            const iosButton = document.getElementById('iosInstallButton');

            if (!options || !androidButton || !iosButton) return;

            const installed = isInstalledDisplayMode();
            const showAndroid = !installed && Boolean(deferredPrompt);
            const showIos = !installed && isIosSafari();

            androidButton.hidden = !showAndroid;
            iosButton.hidden = !showIos;
            options.hidden = !(showAndroid || showIos);
        };

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredPrompt = event;
            syncInstallOptions();
        });

        document.addEventListener('DOMContentLoaded', () => {
            const dialog = document.getElementById('pwaInstallDialog');
            const instructions = document.getElementById('pwaInstallInstructions');
            const options = document.querySelector('.pwa-options');
            syncInstallOptions();
            const showGuide = (platform) => {
                const android = platform === 'android';
                const canPrompt = android && deferredPrompt;
                instructions.innerHTML = `
                    <div class="pwa-guide-mark"><img src="{{ asset('images/myhep-mark.png') }}?v=11" alt=""></div>
                    <p class="pwa-guide-kicker">${android ? 'Android install' : 'iPhone setup'}</p>
                    <h2>${android ? 'Make MyHEP feel like an app' : 'Add MyHEP to your Home Screen'}</h2>
                    <p>${android ? (canPrompt ? 'One more step opens the secure install prompt.' : 'Chrome can still add MyHEP from its browser menu.') : 'Safari keeps this step in its Share menu.'}</p>
                    <div class="pwa-guide-steps">
                        ${android
                            ? (canPrompt ? '<div class="pwa-guide-step"><span class="pwa-guide-number">1</span><span>Continue to open the Android install prompt.</span></div><div class="pwa-guide-step"><span class="pwa-guide-number">2</span><span>Choose <strong>Install</strong> to add MyHEP to your home screen.</span></div>' : '<div class="pwa-guide-step"><span class="pwa-guide-number">1</span><span>Tap Chrome’s three-dot menu.</span></div><div class="pwa-guide-step"><span class="pwa-guide-number">2</span><span>Choose <strong>Install app</strong> or <strong>Add to Home screen</strong>.</span></div>')
                            : '<div class="pwa-guide-step"><span class="pwa-guide-number">1</span><span>Tap the <strong>three dots (...)</strong> or <strong>Share</strong> button at the bottom navigation bar.</span></div><div class="pwa-guide-step"><span class="pwa-guide-number">2</span><span>Tap <strong>Share</strong> &rarr; scroll down / tap <strong>View More</strong> &rarr; select <strong>Add to Home Screen</strong> (+).</span></div><div class="pwa-guide-step"><span class="pwa-guide-number">3</span><span>Tap <strong>Add</strong> at the top right to place MyHEP on your home screen.</span></div>'}
                    </div>
                    <button type="button" class="pwa-guide-action" id="pwaGuideAction">${canPrompt ? 'Continue to install' : (android ? 'I understand' : 'I will use Safari')}</button>`;
                dialog.showModal();
                document.getElementById('pwaGuideAction').addEventListener('click', async () => {
                    if (!android || !deferredPrompt) {
                        dialog.close();
                        return;
                    }
                    deferredPrompt.prompt();
                    await deferredPrompt.userChoice.catch(() => null);
                    deferredPrompt = null;
                    dialog.close();
                });
            };

            document.getElementById('androidInstallButton')?.addEventListener('click', () => showGuide('android'));
            document.getElementById('iosInstallButton')?.addEventListener('click', () => showGuide('ios'));
            document.getElementById('pwaInstallClose')?.addEventListener('click', () => dialog.close());
            window.addEventListener('appinstalled', () => {
                deferredPrompt = null;
                dialog.close();
                syncInstallOptions();
            });
            window.addEventListener('pageshow', syncInstallOptions);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) syncInstallOptions();
            });
            displayModeQueries.forEach((query) => {
                window.matchMedia(query).addEventListener?.('change', syncInstallOptions);
            });
        });
    })();
</script>
@php
    $homeStats = $homeStats ?? [
        'students_managed' => 0,
        'open_actions' => 0,
        'digital_records' => 0,
        'server_time' => now()->format('Y-m-d H:i:s'),
        'system_online' => true,
    ];
@endphp

    <!-- ── HERO ── -->
    <section class="hero">

        <form method="POST" action="{{ route('locale.update') }}" class="lang-switch">
            @csrf
            <select name="locale" onchange="this.form.submit()" aria-label="{{ __('Select language') }}">
                <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>EN</option>
                <option value="ms" {{ app()->getLocale() === 'ms' ? 'selected' : '' }}>BM</option>
            </select>
        </form>

        <div class="hero-content">

            <div class="brand">
                <img src="{{ asset('images/logo-politeknik-besut.png') }}" alt="{{ __('Logo Politeknik Besut') }}" class="brand-logo">
                <div class="brand-divider" aria-hidden="true"></div>
                <div class="brand-text">
                    <h1>{{ __('home.brand_name') }}</h1>
                    <p>{{ __('home.brand_sub') }}</p>
                </div>
            </div>

            <h2 class="headline">
                {{ __('home.headline_prefix') }} <em>{{ __('home.headline_focus') }}</em> {{ __('home.headline_suffix') }}
            </h2>

            <p class="subtitle">{{ __('home.subtitle') }}</p>

            <div class="cta-group">
                <a href="{{ route('login') }}" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                    {{ __('home.login_button') }}
                </a>
                <div class="stat-pill" data-home-live data-live-url="{{ route('system-overview.live') }}" aria-label="{{ __('System status: online') }}">
                    <span class="dot" aria-hidden="true"></span>
                    <span data-home-stat="system-status">{{ __('home.official_label') }} · MyHEP · {{ __('home.live_label') }}</span>
                </div>
            </div>
            <div class="pwa-options" aria-label="{{ __('Install MyHEP') }}" hidden>
                <button type="button" class="pwa-option" id="androidInstallButton" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v2a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2"/></svg>
                    {{ __('Install on Android') }}
                </button>
                <button type="button" class="pwa-option" id="iosInstallButton" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 16V3m0 0 4 4m-4-4L8 7M5 11v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8"/></svg>
                    {{ __('Add to iPhone Home Screen') }}
                </button>
            </div>

        </div>

        <div class="hero-visual" aria-hidden="true">

            <div class="cards-grid">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <!-- Lucide: graduation-cap -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                    </div>
                    <h2>{{ __('home.card_scholarship_title') }}</h2>
                    <p>{{ __('home.card_scholarship_desc') }}</p>
                </div>

                <div class="feature-card">
                    <div class="icon-wrap">
                        <!-- Lucide: shield-check -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    </div>
                    <h2>{{ __('home.card_discipline_title') }}</h2>
                    <p>{{ __('home.card_discipline_desc') }}</p>
                </div>
            </div>

            <div class="badge-row">
                <span>{{ __('home.official_label') }}</span>
                <span class="badge">MyHEP</span>
            </div>

        </div>
    </section>

    <dialog class="pwa-install-dialog" id="pwaInstallDialog" aria-labelledby="pwaInstallTitle">
        <div class="pwa-install-dialog-inner">
            <h2 id="pwaInstallTitle">{{ __('Install MyHEP') }}</h2>
            <div id="pwaInstallInstructions"></div>
            <button type="button" class="pwa-install-dialog-close" id="pwaInstallClose">{{ __('Close') }}</button>
        </div>
    </dialog>

    <!-- ── MAIN CONTENT ── -->
    <main class="content-sections">

        <section class="section-card" aria-labelledby="about-title">
            <h3 class="section-title" id="about-title">{{ __('home.about_title') }}</h3>
            <p class="section-desc">{{ __('home.about_desc') }}</p>
        </section>

        <section class="section-card cta-card" aria-labelledby="cta-title">
            <h3 class="section-title" id="cta-title">{{ __('home.cta_title') }}</h3>
            <p class="section-desc">{{ __('home.cta_desc') }}</p>
            <div class="cta-actions">
                <a href="{{ route('login') }}" class="cta-btn primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                    {{ __('home.cta_login') }}
                </a>
                <a href="{{ route('bug-reports.create') }}" class="cta-btn secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 12h9m-9 4h5.25M6 3.75h8.25L18 7.5v12.75A2.25 2.25 0 0115.75 22.5h-9A2.25 2.25 0 014.5 20.25V6A2.25 2.25 0 016.75 3.75H6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3.75V7.5H18"/></svg>
                    {{ __('home.cta_report') }}
                </a>
                <a href="mailto:support@polibesut.edu.my?subject=MyHEP%20Support" class="cta-btn secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    {{ __('home.cta_contact') }}
                </a>
            </div>
        </section>

        <section class="section-card" aria-labelledby="support-title">
            <h3 class="section-title" id="support-title">{{ __('home.support_title') }}</h3>
            <div class="support-grid">
                <article class="support-box"><h5>{{ __('home.support_email') }}</h5><p>support@polibesut.edu.my</p></article>
                <article class="support-box"><h5>{{ __('home.support_office') }}</h5><p>{{ __('home.support_office_value') }}</p></article>
                <article class="support-box"><h5>{{ __('home.support_phone') }}</h5><p>{{ __('+60 XXX XXX XXX') }}</p></article>
            </div>
        </section>

        <section class="section-card" aria-labelledby="location-title">
            <h3 class="section-title" id="location-title">{{ __('home.location_title') }}</h3>
            <p class="section-desc">{{ __('home.location_desc') }}</p>
            <div class="location-grid">
                <article class="location-box">
                    <span class="location-kicker">{{ __('home.location_kicker') }}</span>
                    <div class="location-name">{{ __('home.location_name') }}</div>
                    <p class="location-address">{{ __('home.location_address') }}</p>
                    <a
                        href="https://www.google.com/maps/search/?api=1&query=Politeknik%20Besut%20Terengganu%2C%20Jalan%20Bukit%20Keluang%2C%2022200%20Besut%2C%20Terengganu%2C%20Malaysia"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="location-link"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6-4.35 6-10.2A6 6 0 006 10.8C6 16.65 12 21 12 21z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 13.5a2.7 2.7 0 100-5.4 2.7 2.7 0 000 5.4z"/></svg>
                        {{ __('home.location_open_map') }}
                    </a>
                </article>
                <div class="map-shell">
                    <iframe
                        title="{{ __('home.location_iframe_title') }}"
                        src="https://maps.google.com/maps?q=Politeknik%20Besut%20Terengganu%2C%20Jalan%20Bukit%20Keluang%2C%2022200%20Besut%2C%20Terengganu%2C%20Malaysia&z=15&output=embed"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
            </div>
        </section>

    </main>

    <footer class="welcome-footer">
        <div class="footer-meta">
            <nav class="footer-links" aria-label="{{ __('Footer navigation') }}">
                <a href="#">{{ __('home.privacy_policy') }}</a>
                <a href="#">{{ __('home.terms_use') }}</a>
            </nav>
            <span>{{ __('home.system_version') }}</span>
        </div>
        <div class="footer-copy">
            &copy; {{ date('Y') }} MyHEP. {{ __('home.copyright') }}
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.querySelector('[data-home-live]');
        if (!root) return;

        const liveUrl = root.dataset.liveUrl;
        const formatter = new Intl.NumberFormat();
        const setText = (name, value) => {
            const el = document.querySelector(`[data-home-stat="${name}"]`);
            if (el) el.textContent = value;
        };

        async function refreshHomeStats() {
            try {
                const response = await fetch(liveUrl, { headers: { Accept: 'application/json' } });
                if (!response.ok) return;

                const payload = await response.json();
                const data = payload.data || {};
                setText('students-managed', formatter.format(Number(data.students_managed || 0)));
                setText('open-actions', formatter.format(Number(data.open_actions || 0)));
                setText('digital-records', formatter.format(Number(data.digital_records || 0)));
                root.setAttribute('aria-label', `System status: ${data.system_online ? 'online' : 'offline'}, updated ${data.server_time || ''}`);
            } catch (error) {
                root.setAttribute('aria-label', 'System status: live update unavailable');
            }
        }

        refreshHomeStats();
        setInterval(refreshHomeStats, 10000);
    });
    </script>
</body>
</html>
