@extends('layouts.app')

@section('title', 'JHEP Laptop Management')
@section('header')<h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('JHEP Laptop Management') }}</h2>@endsection



@section('content')
<div class="laptop-wrap">
    @if(session('success'))<div class="msg-ok">{{ session('success') }}</div>@endif
    <section class="laptop-hero">
        <div><span class="laptop-kicker">{{ __('Asset operations') }}</span><h1>{{ __('JHEP Laptop Loans') }}</h1><p>{{ __('Print each QR code, monitor current loans, and review borrowing and return times.') }}</p></div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap"><a class="laptop-scan-link" href="{{ route('admin.laptops.print') }}" target="_blank">{{ __('Print QR Labels') }}</a><a class="laptop-scan-link" href="{{ route('admin.laptops.scan') }}">{{ __('Scan a Laptop QR') }}</a></div>
    </section>

    <form method="GET" class="laptop-filter">
        <div class="laptop-field"><label for="search">{{ __('Search') }}</label><input id="search" name="search" value="{{ $search }}" placeholder="{{ __('Laptop, asset code, or borrower') }}"></div>
        <div class="laptop-field"><label for="status">{{ __('Status') }}</label><select id="status" name="status"><option value="">{{ __('All statuses') }}</option><option value="available" @selected($status==='available')>{{ __('Available') }}</option><option value="borrowed" @selected($status==='borrowed')>{{ __('Borrowed') }}</option></select></div>
        <button class="laptop-btn" type="submit">{{ __('Filter') }}</button>
        @if($search !== '' || $status !== '')<a class="laptop-btn" href="{{ route('admin.laptops.index') }}">{{ __('Clear') }}</a>@endif
    </form>

    <div class="laptop-grid">
        @foreach($laptops as $laptop)
            @php
                $scanUrl = route('laptops.borrow', ['token' => $laptop->qr_token]);
            @endphp
            <article class="laptop-card">
                <div class="laptop-card-head"><div><h2>{{ $laptop->name }}</h2><div class="laptop-code">{{ $laptop->asset_code }}</div></div><span class="laptop-status {{ $laptop->status }}">{{ $laptop->status }}</span></div>
                <div class="laptop-borrower">
                    @if($laptop->borrower_name)
                        <strong>{{ $laptop->borrower_name }}</strong>Borrowed {{ \Illuminate\Support\Carbon::parse($laptop->borrowed_at)->format('d M Y, h:i A') }}
                    @else
                        <strong>{{ __('Ready to borrow') }}</strong>No current borrower
                    @endif
                </div>
                <a class="laptop-qr" href="{{ $scanUrl }}" title="{{ __('Open public borrowing page') }}"><img loading="lazy" alt="QR code for {{ $laptop->name }}" src="https://api.qrserver.com/v1/create-qr-code/?size=360x360&data={{ urlencode($scanUrl) }}"></a>
            </article>
        @endforeach
    </div>

    <section class="laptop-panel">
        <div class="laptop-panel-head"><h2>{{ __('Borrowing history') }}</h2><p>{{ __('Every borrow and return is recorded with the authenticated staff account.') }}</p></div>
        <div class="laptop-table-wrap"><table class="laptop-table"><thead><tr><th>{{ __('Laptop') }}</th><th>{{ __('Staff') }}</th><th>{{ __('Borrowed') }}</th><th>{{ __('Returned') }}</th><th>{{ __('Duration') }}</th></tr></thead><tbody>
        @forelse($history as $loan)
            <tr><td><strong>{{ $loan->laptop_name }}</strong><br><small>{{ $loan->asset_code }}</small></td><td>{{ $loan->staff_name }}</td><td>{{ \Illuminate\Support\Carbon::parse($loan->borrowed_at)->format('d M Y, h:i A') }}</td><td>{{ $loan->returned_at ? \Illuminate\Support\Carbon::parse($loan->returned_at)->format('d M Y, h:i A') : 'Still borrowed' }}</td><td>{{ $loan->returned_at ? \Illuminate\Support\Carbon::parse($loan->borrowed_at)->diffForHumans(\Illuminate\Support\Carbon::parse($loan->returned_at), true) : '-' }}</td></tr>
        @empty<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted)">{{ __('No laptop activity recorded yet.') }}</td></tr>@endforelse
        </tbody></table></div>
    </section>
    {{ $history->links() }}
</div>
@endsection
