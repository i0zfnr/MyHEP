@extends('layouts.app')

@section('title', __('Rekod Scholarship'))



@section('header')
    <h2 class="sch-record-page-title">{{ __('Rekod Scholarship') }}</h2>
@endsection

@section('content')
<div class="wrap sch-record-page">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="sch-record-card">
        <div class="sch-record-head">
            <h1>{{ __('Pengurusan Rekod Scholarship') }}</h1>
            <div class="sch-record-actions">
                <a class="sch-record-btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="sch-record-btn" href="{{ route('admin.scholarships.export', request()->query()) }}">{{ __('Export CSV') }}</a>
                <span class="sch-record-btn is-disabled" aria-disabled="true" title="{{ __('Unavailable') }}">{{ __('Unavailable') }}</span>
            </div>
        </div>

        <div class="sch-record-filters" data-filter-sheet data-filter-title="{{ __('Scholarship filters') }}">
            <form method="GET" action="{{ route('admin.scholarships.index') }}">
                <div class="sch-record-filter-grid">
                    <div class="sch-record-field sch-record-search">
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari nama/matrik/penyedia') }}">
                    </div>
                    <div class="sch-record-field">
                        <select name="type">
                            <option value="">{{ __('Semua jenis') }}</option>
                            @foreach(['scholarship','welfare','sponsorship','none'] as $type)
                                <option value="{{ $type }}" {{ ($filters['type'] ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sch-record-field">
                        <select name="status">
                            <option value="">{{ __('Semua status') }}</option>
                            @foreach(['pending','confirmed','rejected'] as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ __($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sch-record-filter-actions">
                        <button class="sch-record-btn" type="submit">{{ __('Filter') }}</button>
                        <a class="sch-record-btn" href="{{ route('admin.scholarships.index') }}">{{ __('Reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="sch-record-table-wrap" data-lenis-prevent>
            <table class="sch-record-table">
                <thead>
                    <tr>
                        <th>{{ __('Pelajar') }}</th>
                        <th>{{ __('Jenis') }}</th>
                        <th>{{ __('Penyedia') }}</th>
                        <th>Jumlah (RM)</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Tindakan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td data-label="{{ __('Pelajar') }}">
                                <span class="sch-record-student">{{ $record->student_name }}</span>
                                <span class="sch-record-meta">{{ $record->matric_no }}</span>
                            </td>
                            <td data-label="{{ __('Jenis') }}">{{ $record->type }}</td>
                            <td data-label="{{ __('Penyedia') }}">{{ $record->provider_name ?: '-' }}</td>
                            <td data-label="{{ __('Jumlah (RM)') }}">{{ $record->amount !== null ? number_format((float)$record->amount, 2) : '-' }}</td>
                            <td data-label="{{ __('Status') }}"><span class="pill {{ strtolower($record->status) }}">{{ __($record->status) }}</span></td>
                            <td data-label="{{ __('Tindakan') }}">
                                <div class="sch-record-row-actions">
                                    <a class="sch-record-btn" href="{{ route('admin.scholarships.edit', $record->id) }}">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.scholarships.destroy', $record->id) }}" class="sch-record-delete-form"
                                        data-confirm-title="{{ __('Delete scholarship record') }}"
                                        data-confirm-message="{{ __('Delete this scholarship record?') }}"
                                        data-confirm-action="{{ __('Delete') }}"
                                        data-confirm-tone="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button class="sch-record-btn is-danger" type="submit">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="sch-record-empty">{{ __('Tiada rekod scholarship.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="sch-pagination">{{ $records->onEachSide(1)->links('vendor.pagination.myhep') }}</div>
</div>
@endsection


