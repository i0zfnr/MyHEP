@extends('layouts.app')

@section('title', 'Peraturan Disiplin')



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Peraturan Disiplin') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="card">
        <div class="head">
            <h1 style="margin:0;font-size:20px;">{{ __('Pengurusan Peraturan') }}</h1>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="btn" href="{{ route('admin.rules.export', request()->query()) }}">{{ __('Export CSV') }}</a>
                <a class="btn" href="{{ route('admin.rules.create') }}">{{ __('Tambah Peraturan') }}</a>
            </div>
        </div>

        <div class="filters" data-filter-sheet data-filter-title="{{ __('Rule filters') }}">
            <form method="GET" action="{{ route('admin.rules.index') }}">
                <div class="filter-grid">
                    <div><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari tajuk / penerangan') }}"></div>
                    <div>
                        <select name="category_id" style="width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:8px 10px; font-size:13px; background:#fff;">
                            <option value="">{{ __('Semua kategori') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string)($filters['category_id'] ?? '') === (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn" type="submit">{{ __('Filter') }}</button>
                        <a class="btn" href="{{ route('admin.rules.index') }}">{{ __('Reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Tajuk') }}</th>
                        <th>{{ __('Kategori') }}</th>
                        <th>{{ __('Penerangan') }}</th>
                        <th>{{ __('Kemaskini Oleh') }}</th>
                        <th>{{ __('Tindakan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td>{{ $rule->title }}</td>
                            <td>{{ $rule->category_name }}</td>
                            <td style="max-width:420px;">{{ $rule->description }}</td>
                            <td>{{ $rule->updated_by_name ?: '-' }}</td>
                            <td>
                                <div class="actions-cell">
                                    <a class="btn" href="{{ route('admin.rules.edit', $rule->id) }}">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.rules.destroy', $rule->id) }}" style="margin:0;"
                                        data-confirm-title="{{ __('Delete rule') }}"
                                        data-confirm-message="{{ __('Delete this rule?') }}"
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
                        <tr><td colspan="5" style="text-align:center;color:#7a6555;">{{ __('Tiada peraturan.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px;">{{ $rules->links() }}</div>
</div>
@endsection


