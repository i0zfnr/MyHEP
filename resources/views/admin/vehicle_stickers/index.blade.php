@extends('layouts.app')

@section('title', __('Permohonan Sticker Kenderaan'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Permohonan Sticker Kenderaan') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="msg-ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="msg-err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="card">
        <div class="head">
            <h1 style="margin:0;font-size:20px;">{{ __('Permohonan Sticker Kenderaan') }}</h1>
            <div style="display:flex; gap:8px; flex-wrap:wrap;"><a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a><a class="btn" href="{{ route('admin.vehicle-stickers.export', request()->query()) }}">{{ __('Export CSV') }}</a></div>
        </div>

        <div class="filters" data-filter-sheet data-filter-title="{{ __('Vehicle sticker filters') }}">
            <form method="GET" action="{{ route('admin.vehicle-stickers.index') }}" data-live-filter-form data-live-filter-delay="350">
                <div class="filter-grid">
                    <div><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari nama pelajar / matrik / no kenderaan') }}"></div>
                    <div>
                        <select name="status">
                            <option value="">{{ __('Semua status') }}</option>
                            @foreach(['pending','approved','rejected'] as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ __($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span data-live-filter-status aria-live="polite" style="font-size:.75rem;color:var(--text-muted);"></span>
                </div>
            </form>
        </div>

        <div data-live-filter-results>
        <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>{{ __('Pelajar') }}</th><th>{{ __('Kenderaan') }}</th><th>{{ __('Dokumen') }}</th><th>{{ __('Status') }}</th><th>{{ __('Disemak Oleh') }}</th><th>{{ __('Tarikh') }}</th><th>{{ __('Tindakan') }}</th></tr></thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td>{{ $app->student_name }}<br><span style="color:#7a6555">{{ $app->matric_no }}</span></td>
                            <td>{{ $app->vehicle_no }}<br><span style="color:#7a6555">{{ $app->vehicle_type }}</span></td>
                            <td>
                                @if($app->license_card_path)
                                    <a class="doc-link" href="{{ asset('storage/' . $app->license_card_path) }}" target="_blank">{{ __('Kad Lesen') }}</a><br>
                                @endif
                                @if($app->parent_permission_path)
                                    <a class="doc-link" href="{{ asset('storage/' . $app->parent_permission_path) }}" target="_blank">{{ __('Surat Ibu Bapa') }}</a><br>
                                @endif
                                @if($app->vehicle_photo_path)
                                    <a class="doc-link" href="{{ asset('storage/' . $app->vehicle_photo_path) }}" target="_blank">{{ __('Gambar Kenderaan') }}</a>
                                    <img src="{{ asset('storage/' . $app->vehicle_photo_path) }}" alt="{{ __('Vehicle plate image') }}" class="doc-thumb">
                                @endif
                            </td>
                            <td><span class="status {{ $app->status }}">{{ __($app->status) }}</span></td>
                            <td>{{ $app->approved_by_name ?: '-' }}</td>
                            <td>{{ $app->created_at ? \Illuminate\Support\Carbon::parse($app->created_at)->format('Y-m-d') : '-' }}</td>
                            <td>
                                <div class="actions-cell">
                                @if($app->status === 'pending')
                                    <div class="decision">
                                        <form method="POST" action="{{ route('admin.vehicle-stickers.decision', $app->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-primary" type="submit">{{ __('Lulus') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.vehicle-stickers.decision', $app->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn btn-danger" type="submit">{{ __('Tolak') }}</button>
                                        </form>
                                    </div>
                                @endif
                                    <form method="POST" action="{{ route('admin.vehicle-stickers.destroy', $app->id) }}" style="margin:0;"
                                        data-confirm-title="{{ __('Delete application') }}"
                                        data-confirm-message="{{ __('Delete this vehicle sticker application?') }}"
                                        data-confirm-action="{{ __('Delete') }}"
                                        data-confirm-tone="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:#7a6555;">{{ __('Tiada permohonan sticker.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:14px;">{{ $applications->links() }}</div>
        </div>
    </div>
</div>
@endsection


