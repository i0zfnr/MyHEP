@extends('layouts.app')

@section('title', 'JHEP Laptop Management')
@section('header')<h2 style="margin:0;font-size:1rem;font-weight:700;">JHEP Laptop Management</h2>@endsection

@push('styles')
<style>
    .laptop-wrap{max-width:1180px;margin:0 auto;display:grid;gap:1rem}.laptop-hero{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.4rem;border-radius:20px;background:linear-gradient(135deg,#3b291d,#765237 60%,#a77950);color:#fff;box-shadow:var(--glass-shadow)}.laptop-hero h1{margin:.25rem 0;font-size:1.65rem}.laptop-hero p{margin:0;color:rgba(255,255,255,.76)}.laptop-kicker{font-size:.68rem;font-weight:800;letter-spacing:.11em;text-transform:uppercase;color:#f2d5b5}.laptop-scan-link{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:.7rem 1rem;border:1px solid rgba(255,255,255,.3);border-radius:11px;background:rgba(255,255,255,.14);color:#fff;text-decoration:none;font-weight:800;white-space:nowrap}.laptop-filter,.laptop-panel{border:1px solid var(--glass-line);border-radius:18px;background:var(--glass-bg-strong);box-shadow:var(--glass-shadow);overflow:hidden}.laptop-filter{display:grid;grid-template-columns:1fr 220px auto auto;gap:.7rem;padding:1rem;align-items:end}.laptop-field{display:grid;gap:.35rem}.laptop-field label{font-size:.68rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted)}.laptop-field input,.laptop-field select{min-height:44px;width:100%;padding:.7rem .8rem;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font:inherit}.laptop-btn{min-height:44px;display:inline-flex;align-items:center;justify-content:center;padding:.65rem .9rem;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);text-decoration:none;font-weight:800;cursor:pointer}.laptop-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem}.laptop-card{padding:1rem;border:1px solid var(--glass-line);border-radius:16px;background:var(--glass-bg-strong);box-shadow:var(--glass-shadow)}.laptop-card-head{display:flex;justify-content:space-between;gap:.5rem}.laptop-card h2{margin:0;font-size:1rem;color:var(--text)}.laptop-code{margin-top:.2rem;font-size:.68rem;color:var(--text-muted)}.laptop-status{align-self:flex-start;padding:.25rem .55rem;border-radius:999px;font-size:.65rem;font-weight:800;text-transform:capitalize}.laptop-status.available{background:#e7f4ee;color:#287352}.laptop-status.borrowed{background:#fff0d9;color:#9a5d10}body[data-theme="dark"] .laptop-status.available{background:rgba(46,160,112,.18);color:#8ee0bb}body[data-theme="dark"] .laptop-status.borrowed{background:rgba(224,153,48,.18);color:#f5c77b}.laptop-borrower{min-height:44px;margin:.8rem 0;padding:.65rem;border-radius:10px;background:color-mix(in srgb,var(--primary) 7%,var(--surface));font-size:.73rem;color:var(--text-muted)}.laptop-borrower strong{display:block;color:var(--text);margin-bottom:.15rem}.laptop-qr{display:grid;place-items:center;padding:.65rem;border-radius:12px;background:#fff}.laptop-qr img{display:block;width:100%;max-width:170px;aspect-ratio:1}.laptop-panel-head{padding:1rem;border-bottom:1px solid var(--glass-line)}.laptop-panel-head h2{margin:0;color:var(--text);font-size:1.05rem}.laptop-panel-head p{margin:.25rem 0 0;color:var(--text-muted);font-size:.75rem}.laptop-table-wrap{overflow:auto}.laptop-table{width:100%;min-width:760px;border-collapse:collapse}.laptop-table th,.laptop-table td{padding:.8rem 1rem;border-bottom:1px solid var(--glass-line);text-align:left;color:var(--text);font-size:.78rem}.laptop-table th{font-size:.65rem;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);background:color-mix(in srgb,var(--primary) 7%,var(--surface))}@media(max-width:900px){.laptop-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:640px){.laptop-hero{display:grid}.laptop-scan-link{width:100%}.laptop-filter{grid-template-columns:1fr 1fr}.laptop-field{grid-column:1/-1}.laptop-grid{grid-template-columns:1fr}.laptop-qr img{max-width:190px}}
    .laptop-hero h1{color:#fff!important;text-shadow:0 1px 2px rgba(0,0,0,.18)}
    .laptop-hero p{color:rgba(255,255,255,.82)!important}
</style>
@endpush

@section('content')
<div class="laptop-wrap">
    @if(session('success'))<div class="msg-ok">{{ session('success') }}</div>@endif
    <section class="laptop-hero">
        <div><span class="laptop-kicker">Asset operations</span><h1>JHEP Laptop Loans</h1><p>Print each QR code, monitor current loans, and review borrowing and return times.</p></div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap"><a class="laptop-scan-link" href="{{ route('admin.laptops.print') }}" target="_blank">Print QR Labels</a><a class="laptop-scan-link" href="{{ route('admin.laptops.scan') }}">Scan a Laptop QR</a></div>
    </section>

    <form method="GET" class="laptop-filter">
        <div class="laptop-field"><label for="search">Search</label><input id="search" name="search" value="{{ $search }}" placeholder="Laptop, asset code, or borrower"></div>
        <div class="laptop-field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option><option value="available" @selected($status==='available')>Available</option><option value="borrowed" @selected($status==='borrowed')>Borrowed</option></select></div>
        <button class="laptop-btn" type="submit">Filter</button>
        @if($search !== '' || $status !== '')<a class="laptop-btn" href="{{ route('admin.laptops.index') }}">Clear</a>@endif
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
                        <strong>Ready to borrow</strong>No current borrower
                    @endif
                </div>
                <a class="laptop-qr" href="{{ $scanUrl }}" title="Open public borrowing page"><img loading="lazy" alt="QR code for {{ $laptop->name }}" src="https://api.qrserver.com/v1/create-qr-code/?size=360x360&data={{ urlencode($scanUrl) }}"></a>
            </article>
        @endforeach
    </div>

    <section class="laptop-panel">
        <div class="laptop-panel-head"><h2>Borrowing history</h2><p>Every borrow and return is recorded with the authenticated staff account.</p></div>
        <div class="laptop-table-wrap"><table class="laptop-table"><thead><tr><th>Laptop</th><th>Staff</th><th>Borrowed</th><th>Returned</th><th>Duration</th></tr></thead><tbody>
        @forelse($history as $loan)
            <tr><td><strong>{{ $loan->laptop_name }}</strong><br><small>{{ $loan->asset_code }}</small></td><td>{{ $loan->staff_name }}</td><td>{{ \Illuminate\Support\Carbon::parse($loan->borrowed_at)->format('d M Y, h:i A') }}</td><td>{{ $loan->returned_at ? \Illuminate\Support\Carbon::parse($loan->returned_at)->format('d M Y, h:i A') : 'Still borrowed' }}</td><td>{{ $loan->returned_at ? \Illuminate\Support\Carbon::parse($loan->borrowed_at)->diffForHumans(\Illuminate\Support\Carbon::parse($loan->returned_at), true) : '-' }}</td></tr>
        @empty<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted)">No laptop activity recorded yet.</td></tr>@endforelse
        </tbody></table></div>
    </section>
    {{ $history->links() }}
</div>
@endsection
