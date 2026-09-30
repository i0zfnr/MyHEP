@extends('layouts.app')

@section('title', __('Senarai Kesalahan'))



@section('header')
    <div class="offense-page-heading">
        <span>{{ __('Hal Ehwal Pelajar') }} <i aria-hidden="true">/</i> {{ __('Disiplin') }}</span>
        <h2>{{ __('Senarai Kesalahan Pelajar') }}</h2>
    </div>
@endsection

@section('content')
<div class="wrap offense-list-page">
    @php($canManageOffenses = adminCan('discipline'))
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="card offense-list-card">
        <div class="head offense-list-head">
            <div class="offense-list-title">
                <h1>{{ __('Rekod Kesalahan') }}</h1>
                <p>{{ __('Cari rekod, semak bukti dan urus tindakan kesalahan pelajar.') }}</p>
            </div>
            <div class="offense-list-actions">
                <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                @if(in_array(session('auth_user.admin_role'), ['system_admin', 'student_affairs_head'], true))
                    <a class="btn" href="{{ route('admin.discipline.dashboard') }}">{{ __('Discipline Dashboard') }}</a>
                @endif
                @if($canManageOffenses)
                    <a class="btn" href="{{ route('admin.offenses.export', request()->query()) }}">{{ __('Export CSV') }}</a>
                @endif
                <a class="btn" href="{{ route('admin.offenses.create') }}">{{ __('Daftar Kesalahan') }}</a>
            </div>
        </div>
        <div class="filters offense-list-filters" data-filter-sheet data-filter-title="{{ __('Offense filters') }}">
            <form method="GET" action="{{ route('admin.offenses.index') }}" data-live-filter-form data-live-filter-delay="350">
                <div class="filter-grid offense-filter-grid">
                    <div class="offense-filter-field offense-search-field">
                        <label for="offenseSearch">{{ __('Carian') }}</label>
                        <input id="offenseSearch" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari nama pelajar / matrik / tempat') }}">
                    </div>
                    <div class="offense-filter-field">
                        <label for="offenseStatus">{{ __('Status') }}</label>
                        <select id="offenseStatus" name="status">
                            <option value="">{{ __('Semua status') }}</option>
                            <option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>{{ __('unpaid') }}</option>
                            <option value="applied" {{ ($filters['status'] ?? '') === 'applied' ? 'selected' : '' }}>{{ __('applied') }}</option>
                            <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>{{ __('paid') }}</option>
                        </select>
                    </div>
                    <div class="offense-filter-field">
                        <label for="offenseDateFrom">{{ __('Tarikh dari') }}</label>
                        <input id="offenseDateFrom" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="offense-filter-field">
                        <label for="offenseDateTo">{{ __('Tarikh hingga') }}</label>
                        <input id="offenseDateTo" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <span class="offense-filter-status" data-live-filter-status aria-live="polite"></span>
                </div>
            </form>
        </div>
        <div data-live-filter-results>
        <div class="offense-table-wrap">
            <table class="offense-table">
                <thead><tr><th>{{ __('Pelajar') }}</th><th>{{ __('No. Matrik') }}</th><th>{{ __('Tarikh') }}</th><th>{{ __('Masa') }}</th><th>{{ __('Tempat') }}</th><th>{{ __('Bukti') }}</th><th>{{ __('Denda (RM)') }}</th><th>{{ __('Status') }}</th><th>{{ __('Tindakan') }}</th></tr></thead>
                <tbody>
                    @forelse($offenses as $offense)
                        <tr>
                            <td data-label="{{ __('Pelajar') }}" class="offense-student-cell">{{ $offense->student_name }}</td>
                            <td data-label="{{ __('No. Matrik') }}">{{ $offense->matric_no }}</td>
                            <td data-label="{{ __('Tarikh') }}">{{ $offense->offense_date }}</td>
                            <td data-label="{{ __('Masa') }}">{{ $offense->offense_time }}</td>
                            <td data-label="{{ __('Tempat') }}">{{ $offense->place }}</td>
                            <td data-label="{{ __('Bukti') }}" class="offense-evidence-cell">
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
                            <td data-label="{{ __('Denda (RM)') }}" class="offense-fine-cell">{{ number_format((float)$offense->fine_amount, 2) }}</td>
                            <td data-label="{{ __('Status') }}"><span class="status {{ $offense->status }}">{{ __($offense->status) }}</span></td>
                            <td data-label="{{ __('Tindakan') }}" class="offense-actions-cell">
                                <div class="actions-cell offense-row-actions">
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
                        <tr><td colspan="9" class="offense-empty">{{ __('Tiada rekod kesalahan.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="offense-pagination">{{ $offenses->links() }}</div>
        </div>
    </div>
</div>
@endsection


