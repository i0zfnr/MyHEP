@extends('layouts.app')

@section('title', $title)



@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ $title }}</h2>
@endsection

@section('content')
<div class="account-wrap">
    @if(session('success'))<div class="msg-ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="msg-err">{{ $errors->first() }}</div>@endif
    @if(session('import_errors'))<div class="account-import-errors"><strong>{{ __('Some rows need attention') }}</strong><ul>@foreach(session('import_errors') as $importError)<li>{{ $importError }}</li>@endforeach</ul></div>@endif

    <section class="account-hero">
        <div class="account-hero-copy">
            <span class="account-eyebrow">{{ __('Account administration') }}</span>
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>
        </div>
        <div class="account-hero-stat"><strong>{{ $totalAccounts ?? $accounts->total() }}</strong><span>{{ $mode === 'staff' ? 'Total staff' : 'Total accounts' }}</span></div>
    </section>

    <section class="account-card">
        <div class="account-toolbar">
            <div class="account-toolbar-head">
                <div><h2>Find {{ $mode === 'staff' ? 'staff members' : 'guards' }}</h2><p>{{ __('Search and manage account access from one place.') }}</p></div>
                <div class="account-toolbar-actions">
                    @if($mode === 'staff')<button class="account-btn" type="button" data-staff-import-open>{{ __('Import Staff') }}</button>@endif
                    <a class="account-btn primary" href="{{ $createRoute }}">+ Add {{ $mode === 'staff' ? 'Staff' : 'Guard' }}</a>
                </div>
            </div>
            <form method="GET" class="account-filters {{ $mode === 'guard' ? 'guard' : '' }}" data-live-filter-form data-live-filter-delay="300">
                <div class="account-field">
                    <label for="search">{{ __('Search') }}</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Name, IC, email, or position') }}">
                </div>
                @if($mode === 'staff')
                    <div class="account-field">
                        <label for="department">{{ __('Department / Unit') }}</label>
                        <select id="department" name="department">
                            <option value="">{{ __('All departments') }}</option>
                            @foreach($departments as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['department'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button class="account-btn" type="submit">{{ __('Filter') }}</button>
                @if(!empty($filters['search']) || !empty($filters['department']))<a class="account-btn" href="{{ url()->current() }}">{{ __('Clear') }}</a>@endif
            </form>
        </div>

        <div data-live-filter-results>
        <div class="account-table-wrap" data-no-virtual>
            <table class="account-table">
                <thead><tr><th>{{ $mode === 'staff' ? 'Staff member' : 'Account' }}</th><th>{{ __('IC Number') }}</th>@if($mode === 'staff')<th>{{ __('Position') }}</th><th>{{ __('Department / Unit') }}</th>@endif<th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @php($currentDepartment = null)
                    @forelse($accounts as $account)
                        @if($mode === 'staff' && $currentDepartment !== ($account->staff_department ?? 'other'))
                            @php($currentDepartment = $account->staff_department ?? 'other')
                            <tr class="account-department-row"><td colspan="6">{{ $departments[$currentDepartment] ?? 'Other Staff' }}</td></tr>
                        @endif
                        <tr>
                            <td data-label="Account"><div class="account-name">{{ $account->full_name }}</div><div class="account-meta">{{ $account->email ?: 'No email provided' }}</div></td>
                            <td data-label="IC number">{{ maskIdentityNumber($account->ic_no) }}</td>
                            @if($mode === 'staff')<td data-label="Position"><span class="account-position">{{ $account->position ?: 'Not specified' }}</span></td><td data-label="Department"><span class="account-category">{{ $departments[$account->staff_department] ?? 'Other Staff' }}</span></td>@endif
                            <td data-label="Status"><span class="account-status {{ $account->is_active ? 'active' : 'inactive' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td data-label="Actions"><div class="account-actions">
                                <a class="account-btn" href="{{ $mode === 'staff' ? route('admin.staff.edit',$account->id) : route('admin.guards.edit',$account->id) }}">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ $mode === 'staff' ? route('admin.staff.reset-password',$account->id) : route('admin.guards.reset-password',$account->id) }}" data-confirm-title="{{ __('Reset password') }}" data-confirm-message="Reset this account password to its default value?" data-confirm-action="Reset">@csrf<button class="account-btn" type="submit">{{ __('Reset') }}</button></form>
                                <form method="POST" action="{{ $mode === 'staff' ? route('admin.staff.destroy',$account->id) : route('admin.guards.destroy',$account->id) }}" data-confirm-title="{{ __('Delete account') }}" data-confirm-message="Permanently delete this account?" data-confirm-action="Delete" data-confirm-tone="danger">@csrf @method('DELETE')<button class="account-btn danger" type="submit">{{ __('Delete') }}</button></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td class="account-empty" colspan="{{ $mode === 'staff' ? 6 : 4 }}"><strong>No {{ $mode }} accounts found</strong><span>Try a different search or add the first {{ $mode }} account.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="account-pagination">{{ $accounts->links('vendor.pagination.myhep') }}</div>
        </div>
    </section>
</div>

@if($mode === 'staff')
<dialog class="account-import-dialog" data-staff-import-dialog>
    <div class="account-import-head"><div><h2>{{ __('Import Staff') }}</h2><p>{{ __('Upload the official staff workbook or a structured CSV file.') }}</p></div><button class="account-btn account-import-close" type="button" data-staff-import-close aria-label="{{ __('Close import window') }}">×</button></div>
    <form class="account-import-body" method="POST" action="{{ route('admin.staff.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="account-field"><label for="staff_file">{{ __('Staff file') }}</label><input id="staff_file" type="file" name="staff_file" accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" required></div>
        <ul class="account-import-notes"><li>{{ __('Accepted formats: XLSX and CSV, up to 20 MB.') }}</li><li>{{ __('Recognized fields: Nama, No IC, Jawatan, Email, and Bahagian/Jabatan/Unit.') }}</li><li>{{ __('Department headings in the official Politeknik Besut workbook are detected automatically.') }}</li><li>{{ __('New accounts use') }} <strong>Staff@12345</strong>{{ __('. Existing IC records are updated.') }}</li></ul>
        <div class="account-import-actions"><button class="account-btn" type="button" data-staff-import-close>{{ __('Cancel') }}</button><button class="account-btn primary" type="submit">{{ __('Import Staff') }}</button></div>
    </form>
</dialog>
@endif
@endsection

@if($mode === 'staff')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.querySelector('[data-staff-import-dialog]');
    if (!dialog) return;
    document.querySelector('[data-staff-import-open]')?.addEventListener('click', () => dialog.showModal());
    dialog.querySelectorAll('[data-staff-import-close]').forEach((button) => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
});
</script>
@endpush
@endif
