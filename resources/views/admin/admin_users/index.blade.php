@extends('layouts.app')

@section('title', __('Pengurusan Admin'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Pengurusan Admin') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="card">
        <div class="head">
            <h1 style="margin:0;font-size:20px;">{{ $canManageAllAdmins ? __('Senarai Akaun Admin') : __('Lecturer Accounts') }}</h1>
            <div style="display:flex; gap:8px;">
                <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="btn" href="{{ route('admin.admin-users.create') }}">{{ $canManageAllAdmins ? __('Tambah Admin') : __('Add Lecturer') }}</a>
            </div>
        </div>

        <form class="admin-search" method="GET" role="search" data-live-filter-form data-live-filter-delay="500">
            <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search name, IC, or email') }}" aria-label="{{ __('Search admin and lecturer accounts') }}">
            <button class="btn" type="submit">{{ __('Search') }}</button>
            @if($search !== '')<a class="btn" href="{{ route('admin.admin-users.index') }}">{{ __('Clear') }}</a>@endif
        </form>

        <div data-live-filter-results>
        <div class="admin-table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Nama') }}</th>
                        <th>IC</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Tarikh Cipta') }}</th>
                        <th>{{ __('Tindakan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <td>{{ $admin->full_name }}</td>
                            <td>{{ maskIdentityNumber($admin->ic_no) }}</td>
                            <td><span class="role {{ $admin->role }}">{{ adminRoleLabel($admin->role) }}</span></td>
                            <td>{{ $admin->created_at ? \Illuminate\Support\Carbon::parse($admin->created_at)->format('Y-m-d') : '-' }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn" href="{{ route('admin.admin-users.edit', $admin->id) }}">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.admin-users.reset-password', $admin->id) }}" style="margin:0;"
                                        data-confirm-title="{{ __('Reset password') }}"
                                        data-confirm-message="{{ __('Reset this admin password to Admin@12345?') }}"
                                        data-confirm-action="{{ __('Reset Password') }}">
                                        @csrf
                                        <button class="btn btn-warn" type="submit">{{ __('Reset Password') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.admin-users.destroy', $admin->id) }}" style="margin:0;"
                                        data-confirm-title="{{ __('Delete admin') }}"
                                        data-confirm-message="{{ __('Delete this admin account?') }}"
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
                        <tr><td colspan="5" style="text-align:center;color:#7a6555;">{{ __('Tiada rekod admin.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $admins->links('vendor.pagination.myhep') }}</div>
        </div>
    </div>
</div>
@endsection


