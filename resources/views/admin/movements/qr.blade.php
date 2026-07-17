@extends('layouts.app')

@section('title', __('Movement QR Management'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Movement QR Management') }}</h2>
@endsection

@push('styles')
<style>
    .qr-wrap { display:grid; grid-template-columns:1fr; gap:1rem; }
    @media (min-width: 900px) { .qr-wrap { grid-template-columns:.85fr 1.15fr; } }
    .qr-print-sheet {
        display:flex;
        flex-direction:column;
        gap:1rem;
    }
    .qr-box {
        display:grid;
        place-items:center;
        border:1px dashed var(--border);
        border-radius:16px;
        padding:1rem;
        background:#fff;
        min-height:330px;
        background-image:linear-gradient(135deg, rgba(164,141,120,.08), rgba(255,255,255,.96));
    }
    .qr-box img { width:min(280px, 100%); height:auto; }
    .qr-url { width:100%; font-size:.78rem; word-break:break-all; border:1px solid var(--border); border-radius:10px; padding:.75rem; color:var(--text-muted); background:rgba(255,255,255,.62); }
    .qr-meta { display:grid; grid-template-columns:1fr; gap:.8rem; margin-top:1rem; }
    .qr-card-actions { display:flex; gap:.65rem; flex-wrap:wrap; margin-bottom:.9rem; }
    .qr-help { color:var(--text-muted); font-size:.8rem; line-height:1.55; }
    .qr-stamp { color:var(--text-muted); font-size:.76rem; }
    .qr-warning {
        border:1px solid #fed7aa;
        background:#fff7ed;
        color:#9a3412;
        border-radius:12px;
        padding:.85rem 1rem;
        margin-bottom:1rem;
        font-size:.84rem;
        line-height:1.55;
    }
    body[data-theme="dark"] .qr-box,
    body[data-theme="dark"] .qr-url { background:var(--surface); }
</style>
@endpush

@section('content')
@php
    $isValid = $checkpoint && $checkpoint->is_active;
@endphp
<div class="ui-shell">
    @if(session('success'))
        <div class="ui-card"><div class="ui-card-body" style="color:#166534;">{{ session('success') }}</div></div>
    @endif
    @if($errors->any())
        <div class="ui-card"><div class="ui-card-body" style="color:#991b1b;">{{ $errors->first() }}</div></div>
    @endif

    <div class="ui-hero">
        <h3>{{ __('Guard House QR') }}</h3>
        <p>{{ __('Manage the live guard house QR so students scan a valid checkpoint before recording any movement.') }}</p>
    </div>

    <div class="qr-warning">
        {{ __('This QR now rotates immediately after each successful scan. Display it on a live guard-house screen and avoid relying on a static printout.') }}
    </div>

    <div class="qr-wrap">
        <section class="ui-card">
            <div class="ui-card-head">
                <strong id="qrCheckpointName">{{ $checkpoint->name ?? __('Checkpoint') }}</strong>
                <span class="ui-status {{ $isValid ? 'status-confirmed' : 'status-rejected' }}" id="qrCheckpointStatus">{{ $isValid ? __('Active') : __('Inactive') }}</span>
            </div>
            <div class="ui-card-body qr-print-sheet">
                @if($scanUrl)
                    <div class="qr-card-actions">
                        <a class="ui-btn primary" href="{{ route('admin.movements.qr.display') }}" target="_blank" rel="noopener">{{ __('Open Live Display') }}</a>
                    </div>
                    <div class="qr-box" id="qrBox">
                        <img id="qrImage" alt="{{ __('Movement QR Code') }}" src="https://api.qrserver.com/v1/create-qr-code/?size=320x320&data={{ urlencode($scanUrl) }}">
                    </div>
                    <p class="qr-help">{{ __('This QR remains active at all times while the checkpoint is active. It automatically regenerates after every successful student scan.') }}</p>
                    <p class="qr-url" id="qrUrl">{{ $scanUrl }}</p>
                    <p class="qr-stamp" id="qrLastUpdated">{{ __('Waiting for latest QR sync...') }}</p>
                @else
                    <p class="muted">{{ __('Checkpoint is not available.') }}</p>
                @endif
            </div>
        </section>

        <section class="ui-card">
            <div class="ui-card-head">
                <strong>{{ __('QR Controls') }}</strong>
                <a class="ui-btn" href="{{ route('admin.movements.index') }}">{{ __('Movement Records') }}</a>
            </div>
            <div class="ui-card-body">
                <form method="POST" action="{{ route('admin.movements.qr.update') }}" class="ui-actions" style="align-items:end;" data-confirm-message="{{ __('Update the movement QR checkpoint?') }}">
                    @csrf
                    <button class="ui-btn primary" name="action" value="rotate" type="submit">{{ __('Rotate QR') }}</button>
                    <button class="ui-btn" name="action" value="activate" type="submit">{{ __('Activate') }}</button>
                    <button class="ui-btn" name="action" value="deactivate" type="submit">{{ __('Deactivate') }}</button>
                </form>

                <div class="qr-meta">
                    <div class="ui-stat-card"><div class="ui-stat-label">{{ __('Validity') }}</div><div style="font-weight:700;" id="qrValidityMode">{{ __('Always active while checkpoint is active') }}</div></div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const refreshMs = 2000;
    const statusUrl = @json(route('admin.movements.qr.status'));
    const qrImage = document.getElementById('qrImage');
    const qrUrl = document.getElementById('qrUrl');
    const qrStatus = document.getElementById('qrCheckpointStatus');
    const qrName = document.getElementById('qrCheckpointName');
    const qrLastUpdated = document.getElementById('qrLastUpdated');
    let lastScanUrl = qrUrl ? qrUrl.textContent.trim() : '';

    const updateQrPanel = (payload) => {
        const checkpoint = payload?.checkpoint;
        const scanUrl = payload?.scan_url || '';
        if (!checkpoint || !qrStatus || !qrName) {
            return;
        }

        qrName.textContent = checkpoint.name || @json(__('Checkpoint'));
        qrStatus.textContent = checkpoint.is_valid ? @json(__('Active')) : @json(__('Inactive'));
        qrStatus.className = 'ui-status ' + (checkpoint.is_valid ? 'status-confirmed' : 'status-rejected');

        if (scanUrl && qrImage && qrUrl && scanUrl !== lastScanUrl) {
            qrImage.src = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' + encodeURIComponent(scanUrl);
            qrUrl.textContent = scanUrl;
            lastScanUrl = scanUrl;
        }

        if (qrLastUpdated) {
            qrLastUpdated.textContent = @json(__('Latest QR sync: ')) + new Date().toLocaleTimeString();
        }
    };

    const pollQrStatus = async () => {
        if (document.visibilityState !== 'visible') {
            return;
        }

        try {
            const response = await fetch(statusUrl, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (!response.ok) {
                return;
            }

            updateQrPanel(await response.json());
        } catch (error) {
            if (qrLastUpdated) {
                qrLastUpdated.textContent = @json(__('Latest QR sync failed. Retrying...'));
            }
        }
    };

    pollQrStatus();
    window.setInterval(pollQrStatus, refreshMs);
})();
</script>
@endpush
