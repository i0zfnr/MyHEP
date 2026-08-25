@extends('layouts.app')

@section('title', 'System Maintenance')



@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;color:var(--text,#2d1f14);">{{ __('System Maintenance') }}</h2>
@endsection

@section('content')
<div class="maint-wrap">
    @if(session('success'))
        <div class="msg-ok">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="msg-err">{{ $errors->first() }}</div>
    @endif

    <section class="maint-hero">
        <span class="maint-eyebrow {{ $maintenance['enabled'] ? 'on' : 'off' }}">
            {{ $maintenance['enabled'] ? 'Maintenance On' : 'Maintenance Off' }}
        </span>
        <h3>{{ __('Maintenance Control') }}</h3>
        <p>{{ __('Use this page before planned downtime, database work, or system updates. When enabled, normal visitors will see Laravel maintenance mode while admins can continue through the bypass URL.') }}</p>
    </section>

    <div class="maint-grid">
        <section class="maint-card">
            <div class="maint-card-head">{{ __('Current Status') }}</div>
            <div class="maint-card-body">
                <div class="maint-row">
                    <span class="maint-key">{{ __('Mode') }}</span>
                    <span class="maint-val">{{ $maintenance['enabled'] ? 'Maintenance enabled' : 'System public' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Retry After') }}</span>
                    <span class="maint-val">{{ $maintenance['retry'] ? $maintenance['retry'] . ' seconds' : '-' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Server Time') }}</span>
                    <span class="maint-val">{{ $maintenance['server_time'] }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('System Cache') }}</span>
                    <span class="maint-val">{{ $maintenance['cache_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Tracked Cache Keys') }}</span>
                    <span class="maint-val">{{ $maintenance['cache_key_count'] }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Last Cache Clear') }}</span>
                    <span class="maint-val">{{ $maintenance['cache_last_cleared_at'] ?: '-' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Cache Toggle Updated') }}</span>
                    <span class="maint-val">{{ $maintenance['cache_updated_at'] ?: '-' }}</span>
                </div>

                @if($maintenance['bypass_url'])
                    <a class="maint-url" href="{{ $maintenance['bypass_url'] }}" target="_blank" rel="noopener">
                        {{ $maintenance['bypass_url'] }}
                    </a>
                @endif
            </div>
        </section>

        <section class="maint-card">
            <div class="maint-card-head">{{ __('Actions') }}</div>
            <div class="maint-card-body">
                <div class="maint-note">
                    {{ __('Save the bypass URL after enabling maintenance. If you lose browser access while maintenance is enabled, run') }} <strong>{{ __('php artisan up') }}</strong> {{ __('from the terminal.') }}
                </div>

                <div class="maint-actions">
                    @if($maintenance['enabled'])
                        <form method="POST" action="{{ route('admin.maintenance.update') }}">
                            @csrf
                            <input type="hidden" name="action" value="disable">
                            <button type="submit" class="maint-btn ok">{{ __('Disable Maintenance') }}</button>
                        </form>
                    @else
            <form method="POST" action="{{ route('admin.maintenance.update') }}"
                data-confirm-title="{{ __('Enable maintenance mode') }}"
                data-confirm-message="{{ __('Enable maintenance mode now? Visitors will be blocked until it is disabled.') }}"
                data-confirm-action="{{ __('Enable') }}">
                            @csrf
                            <input type="hidden" name="action" value="enable">
                            <button type="submit" class="maint-btn warn">{{ __('Enable Maintenance') }}</button>
                        </form>
                    @endif
                </div>

                <hr style="border:none;border-top:1px dashed rgba(122,101,85,.25);margin:1rem 0;">

                <span class="maint-eyebrow {{ $maintenance['cache_enabled'] ? 'cache-on' : 'cache-off' }}">
                    {{ $maintenance['cache_enabled'] ? 'Cache On' : 'Cache Off' }}
                </span>
                <div class="maint-actions">
                    @if($maintenance['cache_enabled'])
                        <form method="POST" action="{{ route('admin.maintenance.update') }}">
                            @csrf
                            <input type="hidden" name="action" value="cache_disable">
                            <button type="submit" class="maint-btn warn">{{ __('Disable Cache') }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.maintenance.update') }}">
                            @csrf
                            <input type="hidden" name="action" value="cache_enable">
                            <button type="submit" class="maint-btn ok">{{ __('Enable Cache') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <section class="maint-card push-centre">
            <div class="maint-card-head maint-push-head">
                <div class="maint-push-head-main">
                    <span class="maint-push-head-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                    </span>
                    <span class="maint-push-head-copy">
                        <strong>{{ __('Push Notification Centre') }}</strong>
                        <span>{{ __('Test delivery and announce scheduled downtime') }}</span>
                    </span>
                </div>
                <span class="maint-push-badge">{{ __('Web Push') }}</span>
            </div>
            <div class="maint-card-body">
                <div class="maint-push-layout">
                    <div class="maint-push-panel test-panel">
                        <span class="maint-panel-kicker">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3 5 6v5c0 4.7 2.8 8 7 10 4.2-2 7-5.3 7-10V6z"/><path d="m9 12 2 2 4-4"/></svg>
                            {{ __('Private test') }}
                        </span>
                        <h4>{{ __('Test this admin device') }}</h4>
                        <p>{{ __('Send a private test notification only to devices registered under your System Admin account.') }}</p>
                        <div class="maint-stats">
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['current_admin_devices'] }}</strong><span>{{ __('Your devices') }}</span></div>
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['devices'] }}</strong><span>{{ __('All active devices') }}</span></div>
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['students'] }}</strong><span>{{ __('Subscribed students') }}</span></div>
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['admins'] }}</strong><span>{{ __('Subscribed admins') }}</span></div>
                        </div>
                        <form method="POST" action="{{ route('admin.maintenance.push.test') }}">
                            @csrf
                            <button type="submit" class="maint-push-test-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                                {{ __('Send Test Notification') }}
                            </button>
                        </form>
                    </div>

                    <div class="maint-push-panel broadcast-panel">
                        <span class="maint-panel-kicker">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 13V6l16-3v13"/><circle cx="6" cy="17" r="3"/><circle cx="18" cy="18" r="3"/></svg>
                            {{ __('System announcement') }}
                        </span>
                        <h4>{{ __('Announce scheduled maintenance') }}</h4>
                        <p>{{ __('Notify every subscribed student and admin. This announcement does not enable maintenance mode automatically.') }}</p>
                        <form method="POST" action="{{ route('admin.maintenance.push.broadcast') }}"
                            data-confirm-title="{{ __('Send maintenance notification') }}"
                            data-confirm-message="Send this announcement to all subscribed students and admins?"
                            data-confirm-action="Send notification">
                            @csrf
                            <div class="maint-fields two">
                                <div class="maint-field">
                                    <label for="starts_at">{{ __('Maintenance starts') }}</label>
                                    <input id="starts_at" name="starts_at" type="datetime-local" required min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('starts_at', now()->addHour()->startOfHour()->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="maint-field">
                                    <label for="ends_at">Expected completion (optional)</label>
                                    <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}">
                                </div>
                            </div>
                            <div class="maint-field" style="margin-top:.75rem;">
                                <label for="message">Custom message (optional)</label>
                                <textarea id="message" name="message" maxlength="300" placeholder="{{ __('Leave blank to use the automatic message with the selected schedule.') }}">{{ old('message') }}</textarea>
                                <small>{{ __('Maximum 300 characters. The schedule is added automatically only when this field is blank.') }}</small>
                            </div>
                            <div class="maint-actions">
                                <button type="submit" class="maint-broadcast-btn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 4 17 8-17 8 3-8z"/><path d="M7 12h14"/></svg>
                                    {{ __('Send Maintenance Notification') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="maint-card email-centre">
            <div class="maint-card-head">{{ __('Email Delivery Test') }}</div>
            <div class="maint-card-body">
                <div class="maint-email-shell">
                    <div class="maint-email-intro">
                        <div class="maint-email-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6.5h16v11H4z"/><path d="m5 8 7 5 7-5"/></svg>
                        </div>
                        <h4>{{ __('Verify outbound email') }}</h4>
                        <p>{{ __('Send a private message through the same mailer used for password-reset codes. Provider acceptance and inbox delivery are reported separately.') }}</p>
                        <span class="maint-email-status">{{ config('mail.default') }} mailer active</span>
                    </div>
                    <form class="maint-email-form" method="POST" action="{{ route('admin.maintenance.email.test') }}">
                        @csrf
                        <div class="maint-field">
                            <label for="test_email">{{ __('Recipient email') }}</label>
                            <input id="test_email" name="email" type="email" maxlength="150" autocomplete="email" required value="{{ old('email') }}" placeholder="admin@example.com">
                            <small>{{ __('Use an approved test inbox. API keys and mail credentials are never shown here.') }}</small>
                        </div>
                        <div class="maint-actions">
                            <button type="submit" class="maint-email-submit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 4 17 8-17 8 3-8z"/><path d="M7 12h14"/></svg>
                                {{ __('Send Test Email') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
