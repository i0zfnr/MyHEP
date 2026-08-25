@extends('layouts.app')

@section('title', __('Kesalahan Saya'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Offense') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="msg-ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="msg-err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="quick">
        <a class="btn" href="{{ route('student.dashboard') }}">{{ __('Kembali ke Index') }}</a>
        <a class="btn" href="{{ route('student.vehicle-stickers.index') }}">{{ __('Permohonan Sticker') }}</a>
        <a class="btn" href="{{ route('student.rules.index') }}">{{ __('Lihat Peraturan') }}</a>
        <a class="btn" href="{{ route('student.scholarships.index') }}">{{ __('Portal Scholarship') }}</a>
    </div>

    @forelse($offenses as $offense)
        <div class="card offense-card">
            <div class="card-head">
                <div class="offense-meta">
                    <div class="offense-line"><span>{{ __('Date') }}</span> {{ $offense->offense_date }} {{ $offense->offense_time }}</div>
                    <div class="offense-line"><span>{{ __('Location') }}</span> {{ $offense->place }}</div>
                </div>
                <div class="fine-box">
                    <div>
                        <div class="fine-label">{{ __('Fine') }}</div>
                        <div class="fine-amount">RM {{ number_format((float)$offense->fine_amount, 2) }}</div>
                    </div>
                    <span class="status-badge status-{{ strtolower($offense->status) }}">{{ __($offense->status) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="offense-content {{ ($offense->evidence_count ?? 0) > 0 ? 'has-evidence' : '' }}">
                    <div>
                        <div class="offense-section-title">{{ __('Violated Rules') }}</div>
                        <ul class="offense-rules">
                            @foreach(($itemsByOffense[$offense->id] ?? collect()) as $item)
                                <li class="offense-rule">
                                    [{{ __($item->rule_reference) }}] {{ __($item->description) }}
                                    @if($item->note)<small>{{ __('Note') }}: {{ $item->note }}</small>@endif
                                </li>
                            @endforeach
                        </ul>

                        @php $app = $fineAppsByOffense[$offense->id] ?? null; @endphp
                        <div class="payment-actions">
                            <a class="btn" href="{{ route('student.offenses.print', $offense->id) }}" target="_blank" rel="noopener">{{ __('Cetak Saman') }}</a>
                        </div>
                        @if($app)
                            <div class="payment-line">
                                <strong>{{ __('Payment application') }}</strong>
                                <span class="status-badge status-{{ strtolower($app->status) }}">{{ __($app->status) }}</span>
                                @if($app->meeting_date)<span>| {{ __('Date') }}: {{ $app->meeting_date }}</span>@endif
                            </div>
                            @if(!empty($app->receipt_path))
                                <a class="receipt-link" href="{{ asset('storage/' . $app->receipt_path) }}" target="_blank" data-media-viewer data-media-title="{{ __('Payment Receipt') }}">{{ __('View uploaded receipt') }}</a>
                            @endif
                        @elseif($offense->status !== 'paid')
                            <form method="POST" action="{{ route('student.fine-applications.store') }}" style="margin-top:12px;" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="offense_id" value="{{ $offense->id }}">
                                <div class="payment-actions">
                                    @if(config('services.ipayment.url'))
                                        <a class="btn" href="{{ config('services.ipayment.url') }}" target="_blank" rel="noopener">{{ __('Make Payment') }}</a>
                                    @endif
                                </div>
                                <label for="receipt_{{ $offense->id }}" style="font-size:13px; font-weight:600; color:#7a6555; margin-top:10px;">{{ __('Upload payment receipt') }}</label>
                                <input id="receipt_{{ $offense->id }}" type="file" name="payment_receipt" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                                <div class="receipt-hint">{{ __('Pay first using iPayment, then upload the receipt here before applying for payment review.') }}</div>
                                <button class="btn btn-primary" type="submit" style="margin-top:8px;">{{ __('Mohon Bayaran Denda') }}</button>
                            </form>
                        @endif
                    </div>

                    @if(($offense->evidence_count ?? 0) > 0)
                        <div class="evidence-panel">
                            <div class="evidence-title">
                                <span>{{ __('Evidence Photo') }}</span>
                                <span>{{ $offense->evidence_count }} {{ __('image(s)') }}</span>
                            </div>
                            <div class="evidence-grid">
                                @foreach($offense->evidence_photos as $photo)
                                    <a class="evidence-link" href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank" data-media-viewer data-media-title="{{ __('Evidence Photo') }}">
                                        <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="{{ __('Bukti Gambar') }}" class="evidence-img">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card" style="padding:14px; color:#7a6555;">{{ __('Tiada rekod kesalahan.') }}</div>
    @endforelse

    <div style="margin-top:14px;">{{ $offenses->links() }}</div>
</div>
@endsection
