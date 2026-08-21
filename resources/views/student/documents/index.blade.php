@extends('layouts.app')

@section('title', __('Document Centre'))

@push('styles')
<style>
    .docs-shell { width:min(100%,1280px); margin:0 auto; display:grid; gap:16px; }
    .docs-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .docs-stat { position:relative; overflow:hidden; min-height:92px; padding:15px 16px; border:1px solid var(--border); border-radius:15px; background:var(--surface); box-shadow:0 10px 28px rgba(20,15,10,.05); }
    .docs-stat::before { content:""; position:absolute; inset:0 auto 0 0; width:3px; background:var(--primary); opacity:.7; }
    .docs-stat span { display:block; color:var(--text-muted); font-size:.75rem; font-weight:800; text-transform:uppercase; }
    .docs-stat strong { display:block; margin-top:6px; color:var(--text); font-size:1.5rem; }
    .docs-note { color:var(--text-muted); font-size:.74rem; }
    .docs-panel { overflow:hidden; border-radius:17px; }
    .docs-panel-head { display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .docs-panel-title { display:grid; gap:3px; }
    .docs-panel-title span { color:var(--text-muted); font-size:.72rem; }
    .docs-security { padding:6px 9px; border:1px solid var(--border); border-radius:999px; color:var(--text-muted); font-size:.68rem; font-weight:750; }
    .docs-upload-grid { display:grid; grid-template-columns:1.3fr 1fr .7fr; gap:14px; }
    .docs-field { display:grid; gap:6px; }
    .docs-field label { color:var(--text); font-size:.78rem; font-weight:750; }
    .docs-field small { color:var(--text-muted); font-size:.7rem; line-height:1.45; }
    .docs-file { grid-column:1 / -1; }
    .docs-file-input { width:100%; padding:5px; border:1px dashed var(--border); border-radius:12px; background:var(--surface-soft); color:var(--text-muted); font:inherit; font-size:.76rem; }
    .docs-file-input::file-selector-button { min-height:36px; margin-right:10px; padding:0 14px; border:0; border-radius:9px; background:color-mix(in srgb,var(--primary) 14%,var(--surface)); color:var(--text); font:inherit; font-size:.73rem; font-weight:800; cursor:pointer; }
    .docs-submit-row { grid-column:1 / -1; display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .docs-routing { max-width:680px; color:var(--text-muted); font-size:.69rem; line-height:1.45; }
    .docs-table-wrap { overflow-x:auto; }
    .docs-empty { padding:28px 16px !important; text-align:center; color:var(--text-muted); }
    @media(max-width:900px){ .docs-stats{grid-template-columns:1fr 1fr}.docs-upload-grid{grid-template-columns:1fr 1fr}.docs-upload-grid .docs-field:nth-child(3){grid-column:1/-1} }
    @media(max-width:700px){ .docs-shell{gap:12px}.docs-upload-grid{grid-template-columns:1fr}.docs-upload-grid .docs-field:nth-child(3),.docs-file,.docs-submit-row{grid-column:auto}.docs-submit-row{align-items:stretch;flex-direction:column}.docs-submit-row .ui-btn{width:100%;justify-content:center}.docs-security{display:none}.docs-table thead{display:none}.docs-table,.docs-table tbody,.docs-table tr,.docs-table td{display:block;width:100%}.docs-table tr{padding:13px 14px;border-bottom:1px solid var(--border)}.docs-table td{display:grid;grid-template-columns:88px minmax(0,1fr);gap:10px;padding:6px 0;border:0}.docs-table td::before{content:attr(data-label);color:var(--text-muted);font-size:.66rem;font-weight:800;text-transform:uppercase}.docs-table td:first-child{display:block}.docs-table td:first-child::before,.docs-table .docs-empty::before{display:none} }
</style>
@endpush

@section('header')
    <h2>{{ __('Document Centre') }}</h2>
@endsection

@section('content')
<div class="docs-shell">
    @if(session('success'))<div class="se-feedback se-feedback--success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="se-feedback se-feedback--error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="docs-stats">
        <div class="docs-stat"><span>{{ __('Total') }}</span><strong>{{ (int) ($counts->total ?? 0) }}</strong></div>
        <div class="docs-stat"><span>{{ __('Pending') }}</span><strong>{{ (int) ($counts->pending ?? 0) }}</strong></div>
        <div class="docs-stat"><span>{{ __('Approved') }}</span><strong>{{ (int) ($counts->approved ?? 0) }}</strong></div>
        <div class="docs-stat"><span>{{ __('Rejected') }}</span><strong>{{ (int) ($counts->rejected ?? 0) }}</strong></div>
    </div>

    <section class="ui-card docs-panel">
        <div class="ui-card-head docs-panel-head">
            <div class="docs-panel-title"><strong>{{ __('Upload Payment or Supporting Document') }}</strong><span>{{ __('Submit one clearly labelled file for review by the responsible department.') }}</span></div>
            <span class="docs-security">{{ __('Private document') }}</span>
        </div>
        <div class="ui-card-body">
            <form method="POST" action="{{ route('student.documents.store') }}" enctype="multipart/form-data" class="docs-upload-grid">
                @csrf
                <div class="docs-field">
                    <label for="document-title">{{ __('Document Title') }}</label>
                    <input id="document-title" name="title" value="{{ old('title') }}" maxlength="150" required placeholder="{{ __('Example: 2026 Insurance Payment Receipt') }}">
                </div>
                <div class="docs-field">
                    <label for="document-category">{{ __('Payment / Document Type') }}</label>
                    <select id="document-category" name="category" required>
                        <option value="">{{ __('Select type') }}</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" @selected(old('category') === $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="docs-field">
                    <label for="document-expiry">{{ __('Expiry Date (Optional)') }}</label>
                    <input id="document-expiry" type="date" name="expiry_date" value="{{ old('expiry_date') }}" min="{{ today()->toDateString() }}">
                </div>
                <div class="docs-field docs-file">
                    <label for="document-file">{{ __('Document File') }}</label>
                    <input class="docs-file-input" id="document-file" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                    <small>{{ __('PDF, JPG, PNG or WebP. Maximum file size: 10 MB.') }}</small>
                </div>
                <div class="docs-submit-row">
                    <span class="docs-routing">{{ __('The selected document type determines its reviewer. Insurance payments go to Discipline Administration; Student Affairs Head and System Admin retain oversight.') }}</span>
                    <button class="ui-btn primary" type="submit">{{ __('Upload for Review') }}</button>
                </div>
            </form>
        </div>
    </section>

    <section class="ui-card docs-panel">
        <div class="ui-card-head docs-panel-head"><div class="docs-panel-title"><strong>{{ __('My Documents') }}</strong><span>{{ __('Track review status and download your submitted files.') }}</span></div></div>
        <div class="docs-table-wrap">
            <table class="ui-table docs-table">
                <thead><tr><th>{{ __('Document') }}</th><th>{{ __('Category') }}</th><th>{{ __('Expiry') }}</th><th>{{ __('Status') }}</th><th>{{ __('Review') }}</th><th>{{ __('Download') }}</th></tr></thead>
                <tbody>
                @forelse($documents as $document)
                    @php($tone = $document->status === 'approved' ? 'confirmed' : ($document->status === 'rejected' ? 'rejected' : 'pending'))
                    <tr>
                        <td data-label="{{ __('Document') }}"><strong>{{ $document->title }}</strong><br><span class="docs-note">{{ $document->original_name }} · {{ number_format($document->size_bytes / 1024, 1) }} KB</span></td>
                        <td data-label="{{ __('Category') }}">{{ \App\Support\StudentDocumentOptions::categoryLabel($document->category) }}</td>
                        <td data-label="{{ __('Expiry') }}">{{ $document->expiry_date ? \Illuminate\Support\Carbon::parse($document->expiry_date)->format('d M Y') : '-' }}@if($document->expiry_date && \Illuminate\Support\Carbon::parse($document->expiry_date)->isPast())<br><span class="ui-status status-rejected">{{ __('Expired') }}</span>@endif</td>
                        <td data-label="{{ __('Status') }}"><span class="ui-status status-{{ $tone }}">{{ \App\Support\StudentDocumentOptions::statusLabel($document->status) }}</span></td>
                        <td data-label="{{ __('Review') }}">{{ $document->review_note ?: '-' }}@if($document->reviewer_name)<br><span class="docs-note">{{ $document->reviewer_name }}</span>@endif</td>
                        <td data-label="{{ __('Download') }}"><a class="ui-btn" href="{{ route('student.documents.download', $document->id) }}">{{ __('Download') }}</a></td>
                    </tr>
                @empty
                    <tr><td class="docs-empty" colspan="6">{{ __('No documents uploaded yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
    {{ $documents->links('vendor.pagination.myhep') }}
</div>
@endsection
