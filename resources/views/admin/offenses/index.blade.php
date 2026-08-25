@extends('layouts.app')

@section('title', __('Senarai Kesalahan'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Senarai Kesalahan Pelajar') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @php($canManageOffenses = adminCan('discipline'))
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="card">
        <div class="head">
            <h1 style="margin:0;font-size:20px;">{{ __('Senarai Kesalahan Pelajar') }}</h1>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                @if($canManageOffenses)
                    <a class="btn" href="{{ route('admin.offenses.export', request()->query()) }}">{{ __('Export CSV') }}</a>
                @endif
                <a class="btn" href="{{ route('admin.offenses.create') }}">{{ __('Daftar Kesalahan') }}</a>
            </div>
        </div>
        <div class="filters" data-filter-sheet data-filter-title="{{ __('Offense filters') }}">
            <form method="GET" action="{{ route('admin.offenses.index') }}" data-live-filter-form data-live-filter-delay="350">
                <div class="filter-grid">
                    <div>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari nama pelajar / matrik / tempat') }}">
                    </div>
                    <div>
                        <select name="status">
                            <option value="">{{ __('Semua status') }}</option>
                            <option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>{{ __('unpaid') }}</option>
                            <option value="applied" {{ ($filters['status'] ?? '') === 'applied' ? 'selected' : '' }}>{{ __('applied') }}</option>
                            <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>{{ __('paid') }}</option>
                        </select>
                    </div>
                    <div>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <span data-live-filter-status aria-live="polite" style="font-size:.75rem;color:var(--text-muted);"></span>
                </div>
            </form>
        </div>
        <div data-live-filter-results>
        <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>{{ __('Pelajar') }}</th><th>{{ __('No. Matrik') }}</th><th>{{ __('Tarikh') }}</th><th>{{ __('Masa') }}</th><th>{{ __('Tempat') }}</th><th>{{ __('Bukti') }}</th><th>{{ __('Denda (RM)') }}</th><th>{{ __('Status') }}</th><th>{{ __('Tindakan') }}</th></tr></thead>
                <tbody>
                    @forelse($offenses as $offense)
                        <tr>
                            <td>{{ $offense->student_name }}</td>
                            <td>{{ $offense->matric_no }}</td>
                            <td>{{ $offense->offense_date }}</td>
                            <td>{{ $offense->offense_time }}</td>
                            <td>{{ $offense->place }}</td>
                            <td>
                                @if(($offense->evidence_count ?? 0) > 0)
                                    <a class="btn" href="{{ asset('storage/' . $offense->evidence_photos[0]->photo_path) }}" target="_blank" data-media-viewer data-media-title="{{ __('Evidence Photo') }}" style="padding:6px 10px; font-size:12px;">{{ __('Lihat') }} ({{ $offense->evidence_count }})</a>
                                @endif
                                @if(!empty($offense->payment_receipt?->receipt_path))
                                    <a class="btn" href="{{ asset('storage/' . $offense->payment_receipt->receipt_path) }}" target="_blank" data-media-viewer data-media-title="{{ __('Payment Receipt') }}" style="padding:6px 10px; font-size:12px;">{{ __('View Receipt') }}</a>
                                @endif
                                @if(($offense->evidence_count ?? 0) === 0 && empty($offense->payment_receipt?->receipt_path))
                                    <span style="color:#7a6555;">-</span>
                                @endif
                            </td>
                            <td>{{ number_format((float)$offense->fine_amount, 2) }}</td>
                            <td><span class="status {{ $offense->status }}">{{ __($offense->status) }}</span></td>
                            <td>
                                <div class="actions-cell">
                                    @if($canManageOffenses)
                                        <a class="btn" href="{{ route('admin.offenses.edit', $offense->id) }}">{{ __('Edit') }}</a>
                                    @endif
                                    <a class="btn" href="{{ route('admin.offenses.print', $offense->id) }}" target="_blank">{{ __('Print') }}</a>
                                    <a class="btn" href="{{ route('admin.offenses.pdf', $offense->id) }}">PDF</a>

                                    @if($canManageOffenses && $offense->status !== 'paid')
                                        <form method="POST" action="{{ route('admin.offenses.mark-paid', $offense->id) }}" style="margin:0;">
                                            @csrf
                                            <button class="btn btn-success" type="submit">{{ __('Mark Paid') }}</button>
                                        </form>
                                    @endif

                                    @if($canManageOffenses)
                                        <form method="POST" action="{{ route('admin.offenses.destroy', $offense->id) }}" style="margin:0;"
                                            data-confirm-title="{{ __('Delete offense') }}"
                                            data-confirm-message="{{ __('Delete this offense record?') }}"
                                            data-confirm-action="{{ __('Delete') }}"
                                            data-confirm-tone="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit">{{ __('Delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center;color:#7a6555;">{{ __('Tiada rekod kesalahan.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:14px;">{{ $offenses->links() }}</div>
        </div>
    </div>
</div>
@endsection


