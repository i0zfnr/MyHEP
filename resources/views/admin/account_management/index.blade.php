@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .account-wrap{max-width:1180px;margin:0 auto;display:grid;gap:1rem}.account-hero,.account-card{border:1px solid var(--glass-line);border-radius:20px;box-shadow:var(--glass-shadow);overflow:hidden}.account-hero{display:flex;align-items:center;justify-content:space-between;gap:1.5rem;padding:1.45rem 1.55rem;background:linear-gradient(135deg,#3b291d 0%,#765237 58%,#a77950 100%);color:#fff}.account-hero-copy{display:grid;gap:.35rem}.account-eyebrow{font-size:.7rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#f2d5b5}.account-hero h1{margin:0;font-size:clamp(1.45rem,2vw,1.8rem);letter-spacing:-.025em}.account-hero p{margin:0;max-width:700px;color:rgba(255,255,255,.78);line-height:1.55}.account-hero-stat{flex:0 0 auto;min-width:118px;padding:.85rem 1rem;border:1px solid rgba(255,255,255,.22);border-radius:15px;background:rgba(255,255,255,.1);text-align:center;backdrop-filter:blur(8px)}.account-hero-stat strong{display:block;font-size:1.65rem}.account-hero-stat span{font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.75)}.account-card{background:var(--glass-bg-strong);backdrop-filter:blur(var(--glass-blur))}.account-toolbar{display:grid;gap:1rem;padding:1.15rem 1.2rem;border-bottom:1px solid var(--glass-line)}.account-toolbar-head{display:flex;align-items:center;justify-content:space-between;gap:1rem}.account-toolbar-head h2{margin:0;font-size:1.05rem;color:var(--text)}.account-toolbar-head p{margin:.2rem 0 0;font-size:.76rem;color:var(--text-muted)}.account-filters{display:grid;grid-template-columns:minmax(240px,1.4fr) minmax(190px,1fr) auto auto;gap:.7rem;align-items:end}.account-filters.guard{grid-template-columns:minmax(240px,1fr) auto auto}.account-field{display:grid;gap:.35rem}.account-field label{font-size:.69rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em}.account-field input,.account-field select{width:100%;min-height:45px;border:1px solid var(--border);border-radius:11px;padding:.7rem .8rem;background:var(--surface);color:var(--text);font:inherit;outline:none}.account-field input:focus,.account-field select:focus{border-color:var(--primary);box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 18%,transparent)}.account-btn{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:.35rem;border:1px solid var(--border);border-radius:10px;padding:.65rem .9rem;background:var(--surface);color:var(--text);text-decoration:none;font-weight:800;font-size:.78rem;cursor:pointer;white-space:nowrap}.account-btn:hover{border-color:var(--primary);color:var(--primary-dark)}.account-btn.primary{background:var(--primary-dark);color:var(--surface);border-color:var(--primary-dark);box-shadow:0 8px 18px color-mix(in srgb,var(--primary-dark) 24%,transparent)}body[data-theme="dark"] .account-btn.primary{background:var(--primary);color:#21170f}.account-btn.danger{color:var(--danger);border-color:color-mix(in srgb,var(--danger) 42%,var(--border))}.account-table-wrap{overflow:auto}.account-table{width:100%;border-collapse:collapse;min-width:820px}.account-table th,.account-table td{padding:.9rem 1rem;border-bottom:1px solid var(--glass-line);text-align:left;font-size:.8rem;color:var(--text)}.account-table tbody tr:last-child td{border-bottom:0}.account-table tbody tr:hover{background:color-mix(in srgb,var(--primary) 5%,transparent)}.account-table th{background:color-mix(in srgb,var(--primary) 7%,var(--surface));color:var(--text-muted);text-transform:uppercase;font-size:.66rem;letter-spacing:.07em}.account-name{font-weight:800;color:var(--text)}.account-meta{font-size:.71rem;color:var(--text-muted);margin-top:.2rem}.account-status,.account-category{display:inline-flex;border-radius:999px;padding:.27rem .58rem;font-size:.66rem;font-weight:800}.account-status.active{background:#e7f4ee;color:#287352}.account-status.inactive{background:#fdeaea;color:#a33434}body[data-theme="dark"] .account-status.active{background:rgba(46,160,112,.18);color:#8ee0bb}body[data-theme="dark"] .account-status.inactive{background:rgba(220,82,82,.18);color:#ffaaaa}.account-category{background:color-mix(in srgb,var(--primary) 13%,var(--surface));color:var(--primary-dark)}.account-actions{display:flex;gap:.4rem;flex-wrap:wrap}.account-empty{text-align:center!important;color:var(--text-muted)!important;padding:3rem 1rem!important}.account-empty strong{display:block;color:var(--text);font-size:.92rem;margin-bottom:.3rem}.account-empty span{font-size:.76rem}@media(max-width:760px){.account-wrap{gap:.8rem}.account-hero{align-items:flex-start;padding:1.15rem}.account-hero-stat{min-width:88px;padding:.65rem}.account-hero-stat strong{font-size:1.35rem}.account-toolbar{padding:1rem}.account-toolbar-head{align-items:flex-start}.account-filters,.account-filters.guard{grid-template-columns:1fr 1fr}.account-field{grid-column:1/-1}.account-btn{min-height:44px}.account-table{min-width:0}.account-table thead{display:none}.account-table,.account-table tbody,.account-table tr,.account-table td{display:block;width:100%}.account-table tbody{display:grid;gap:.7rem;padding:.8rem}.account-table tr{padding:.2rem .85rem;border:1px solid var(--glass-line);border-radius:14px;background:var(--surface)}.account-table td{display:grid;grid-template-columns:105px 1fr;gap:.75rem;padding:.65rem 0;border-bottom:1px solid var(--glass-line)}.account-table td::before{content:attr(data-label);font-size:.64rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted)}.account-table td:last-child{border-bottom:0}.account-actions{margin-top:-.25rem}.account-empty{display:block!important}.account-empty::before{display:none}.account-empty strong{margin-top:.3rem}}@media(max-width:480px){.account-hero{display:grid}.account-hero-stat{display:flex;align-items:center;justify-content:space-between;text-align:left}.account-hero-stat strong{order:2}.account-toolbar-head{display:grid}.account-toolbar-head .account-btn{width:100%}.account-filters,.account-filters.guard{grid-template-columns:1fr}.account-table td{grid-template-columns:88px 1fr}.account-actions .account-btn{flex:1}}
    .account-hero h1{color:#fff!important;text-shadow:0 1px 2px rgba(0,0,0,.18)}
    .account-hero p{color:rgba(255,255,255,.82)!important}
    .account-hero-stat strong{color:#fff!important}
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ $title }}</h2>
@endsection

@section('content')
<div class="account-wrap">
    @if(session('success'))<div class="msg-ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="msg-err">{{ $errors->first() }}</div>@endif

    <section class="account-hero">
        <div class="account-hero-copy">
            <span class="account-eyebrow">Account administration</span>
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>
        </div>
        <div class="account-hero-stat"><strong>{{ $accounts->total() }}</strong><span>Total accounts</span></div>
    </section>

    <section class="account-card">
        <div class="account-toolbar">
            <div class="account-toolbar-head">
                <div><h2>Find {{ $mode === 'staff' ? 'staff members' : 'guards' }}</h2><p>Search and manage account access from one place.</p></div>
                <a class="account-btn primary" href="{{ $createRoute }}">+ Add {{ $mode === 'staff' ? 'Staff' : 'Guard' }}</a>
            </div>
            <form method="GET" class="account-filters {{ $mode === 'guard' ? 'guard' : '' }}">
                <div class="account-field">
                    <label for="search">Search</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or IC number">
                </div>
                @if($mode === 'staff')
                    <div class="account-field">
                        <label for="category">Staff category</label>
                        <select id="category" name="category">
                            <option value="">All categories</option>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button class="account-btn" type="submit">Filter</button>
                @if(!empty($filters['search']) || !empty($filters['category']))<a class="account-btn" href="{{ url()->current() }}">Clear</a>@endif
            </form>
        </div>

        <div class="account-table-wrap">
            <table class="account-table">
                <thead><tr><th>Account</th><th>IC Number</th>@if($mode === 'staff')<th>Category</th>@endif<th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td data-label="Account"><div class="account-name">{{ $account->full_name }}</div><div class="account-meta">{{ $account->email ?: 'No email provided' }}</div></td>
                            <td data-label="IC number">{{ maskIdentityNumber($account->ic_no) }}</td>
                            @if($mode === 'staff')<td data-label="Category"><span class="account-category">{{ $categories[$account->staff_category] ?? 'General JHEP Staff' }}</span></td>@endif
                            <td data-label="Status"><span class="account-status {{ $account->is_active ? 'active' : 'inactive' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td data-label="Created">{{ $account->created_at ? \Illuminate\Support\Carbon::parse($account->created_at)->format('d M Y') : '-' }}</td>
                            <td data-label="Actions"><div class="account-actions">
                                <a class="account-btn" href="{{ $mode === 'staff' ? route('admin.staff.edit',$account->id) : route('admin.guards.edit',$account->id) }}">Edit</a>
                                <form method="POST" action="{{ $mode === 'staff' ? route('admin.staff.reset-password',$account->id) : route('admin.guards.reset-password',$account->id) }}" data-confirm-title="Reset password" data-confirm-message="Reset this account password to its default value?" data-confirm-action="Reset">@csrf<button class="account-btn" type="submit">Reset</button></form>
                                <form method="POST" action="{{ $mode === 'staff' ? route('admin.staff.destroy',$account->id) : route('admin.guards.destroy',$account->id) }}" data-confirm-title="Delete account" data-confirm-message="Permanently delete this account?" data-confirm-action="Delete" data-confirm-tone="danger">@csrf @method('DELETE')<button class="account-btn danger" type="submit">Delete</button></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td class="account-empty" colspan="{{ $mode === 'staff' ? 6 : 5 }}"><strong>No {{ $mode }} accounts found</strong><span>Try a different search or add the first {{ $mode }} account.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    {{ $accounts->links() }}
</div>
@endsection
