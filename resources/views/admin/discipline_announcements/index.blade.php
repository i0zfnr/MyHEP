@extends('layouts.app')

@section('title', __('Pengumuman Disiplin'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Pengumuman Disiplin') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="card">
        <div class="head">
            <h1 style="margin:0;font-size:20px;">{{ __('Pengurusan Pengumuman Disiplin') }}</h1>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="btn" href="{{ route('admin.discipline-announcements.export', request()->query()) }}">{{ __('Export CSV') }}</a>
                <a class="btn" href="{{ route('admin.discipline-announcements.create') }}">{{ __('Tambah Pengumuman') }}</a>
            </div>
        </div>

        <div class="filters" data-filter-sheet data-filter-title="{{ __('Announcement filters') }}">
            <form method="GET" action="{{ route('admin.discipline-announcements.index') }}">
                <div class="filter-grid">
                    <div><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari tajuk/penerangan') }}"></div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn" type="submit">{{ __('Filter') }}</button>
                        <a class="btn" href="{{ route('admin.discipline-announcements.index') }}">{{ __('Reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Tajuk') }}</th>
                        <th>{{ __('Penerangan') }}</th>
                        <th>{{ __('Dicipta Oleh') }}</th>
                        <th>{{ __('Tarikh') }}</th>
                        <th>{{ __('Tindakan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td style="max-width:420px;">{{ $item->body }}</td>
                            <td>{{ $item->admin_name }}</td>
                            <td>{{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d') : '-' }}</td>
                            <td>
                                <div class="actions-cell">
                                    <a class="btn" href="{{ route('admin.discipline-announcements.edit', $item->id) }}">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.discipline-announcements.destroy', $item->id) }}" style="margin:0;"
                                        data-confirm-title="{{ __('Delete announcement') }}"
                                        data-confirm-message="{{ __('Delete this announcement?') }}"
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
                        <tr><td colspan="5" style="text-align:center;color:#7a6555;">{{ __('Tiada pengumuman disiplin.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px;">{{ $announcements->links() }}</div>
</div>
@endsection



