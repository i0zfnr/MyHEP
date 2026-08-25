@extends('layouts.app')

@section('title', __('Pengumuman Scholarship'))

@section('header')
    <h2 class="sch-ann-page-title">{{ __('Pengumuman Scholarship') }}</h2>
@endsection

@section('content')
<div class="wrap sch-ann-page">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="sch-ann-card">
        <div class="sch-ann-head">
            <h1>{{ __('Pengurusan Pengumuman Scholarship') }}</h1>
            <div class="sch-ann-head-actions">
                <a class="sch-ann-btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="sch-ann-btn" href="{{ route('admin.scholarship-announcements.export', request()->query()) }}">{{ __('Export CSV') }}</a>
                <a class="sch-ann-btn" href="{{ route('admin.scholarship-announcements.create') }}">{{ __('Tambah Pengumuman') }}</a>
            </div>
        </div>

        <div class="sch-ann-filters" data-filter-sheet data-filter-title="{{ __('Announcement filters') }}">
            <form method="GET" action="{{ route('admin.scholarship-announcements.index') }}">
                <div class="sch-ann-filter-grid">
                    <div class="sch-ann-field sch-ann-search">
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari tajuk/penerangan') }}">
                    </div>
                    <div class="sch-ann-field">
                        <select name="type">
                            <option value="">{{ __('Semua jenis') }}</option>
                            @foreach(['scholarship','welfare','general'] as $type)
                                <option value="{{ $type }}" {{ ($filters['type'] ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sch-ann-filter-actions">
                        <button class="sch-ann-btn" type="submit">{{ __('Filter') }}</button>
                        <a class="sch-ann-btn" href="{{ route('admin.scholarship-announcements.index') }}">{{ __('Reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="sch-ann-table-wrap" data-lenis-prevent>
            <table class="sch-ann-table">
                <thead>
                    <tr>
                        <th>{{ __('Tajuk') }}</th>
                        <th>{{ __('Jenis') }}</th>
                        <th>{{ __('Poster') }}</th>
                        <th>{{ __('Hubungi') }}</th>
                        <th>{{ __('Penerangan') }}</th>
                        <th>{{ __('Link') }}</th>
                        <th>{{ __('Dicipta Oleh') }}</th>
                        <th>{{ __('Tindakan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $item)
                        <tr>
                            <td class="sch-ann-title" data-label="{{ __('Tajuk') }}">{{ $item->title }}</td>
                            <td data-label="{{ __('Jenis') }}"><span class="pill {{ $item->type }}">{{ $item->type }}</span></td>
                            <td data-label="{{ __('Poster') }}">
                                @if($item->poster_image)
                                    <img class="sch-ann-poster" src="{{ Storage::disk('public')->url($item->poster_image) }}" alt="{{ __('Poster') }}">
                                @else
                                    <span class="sch-ann-muted">&mdash;</span>
                                @endif
                            </td>
                            <td data-label="{{ __('Hubungi') }}">
                                @if($item->contact_email)<a class="sch-ann-contact" href="mailto:{{ $item->contact_email }}">{{ $item->contact_email }}</a>@endif
                                @if($item->contact_phone)<a class="sch-ann-contact" href="tel:{{ $item->contact_phone }}">{{ $item->contact_phone }}</a>@endif
                                @if(!$item->contact_email && !$item->contact_phone)<span class="sch-ann-muted">&mdash;</span>@endif
                            </td>
                            <td class="sch-ann-description" data-label="{{ __('Penerangan') }}">{{ Str::limit($item->body, 100) }}</td>
                            <td class="sch-ann-link" data-label="{{ __('Link') }}">
                                @if($item->link_url)
                                    <a href="{{ $item->link_url }}" target="_blank" rel="noopener">{{ $item->link_label ?: __('Buka Link') }}</a>
                                @else
                                    <span class="sch-ann-muted">&mdash;</span>
                                @endif
                            </td>
                            <td data-label="{{ __('Dicipta Oleh') }}">{{ $item->admin_name }}</td>
                            <td class="sch-ann-actions" data-label="{{ __('Tindakan') }}">
                                <div class="sch-ann-row-actions">
                                    <a class="sch-ann-btn" href="{{ route('admin.scholarship-announcements.edit', $item->id) }}">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.scholarship-announcements.destroy', $item->id) }}" class="sch-ann-delete-form"
                                        data-confirm-title="{{ __('Delete announcement') }}"
                                        data-confirm-message="{{ __('Delete this announcement?') }}"
                                        data-confirm-action="{{ __('Delete') }}"
                                        data-confirm-tone="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button class="sch-ann-btn is-danger" type="submit">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="sch-ann-empty" colspan="8">{{ __('Tiada pengumuman scholarship.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="ann-pagination">{{ $announcements->onEachSide(1)->links('vendor.pagination.myhep') }}</div>
</div>
@endsection
