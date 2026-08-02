@extends('layouts.app')

@section('title', __('Document Centre'))

@push('styles')
<style>
    .docs-shell { max-width:1180px; margin:0 auto; display:grid; gap:18px; }
    .docs-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .docs-stat { padding:16px; border:1px solid var(--border); border-radius:16px; background:var(--surface); }
    .docs-stat span { display:block; color:var(--text-muted); font-size:.75rem; font-weight:800; text-transform:uppercase; }
    .docs-stat strong { display:block; margin-top:6px; color:var(--text); font-size:1.5rem; }
    .docs-note { color:var(--text-muted); font-size:.78rem; }
    .docs-actions { display:flex; gap:8px; flex-wrap:wrap; }
    @media(max-width:900px){ .docs-stats{grid-template-columns:1fr 1fr} }
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

    <section class="ui-card"><div class="ui-card-body"><strong>{{ __('Document Archive') }}</strong><p class="docs-note">{{ __('Documents appear here after you upload them from the related scholarship, payment, or application page.') }}</p></div></section>

    <section class="ui-card">
        <div class="ui-card-head"><strong>{{ __('My Documents') }}</strong></div>
        <div style="overflow-x:auto;">
            <table class="ui-table">
                <thead><tr><th>{{ __('Document') }}</th><th>{{ __('Category') }}</th><th>{{ __('Expiry') }}</th><th>{{ __('Status') }}</th><th>{{ __('Review') }}</th><th>{{ __('Download') }}</th></tr></thead>
                <tbody>
                @forelse($documents as $document)
                    @php($tone = $document->status === 'approved' ? 'confirmed' : ($document->status === 'rejected' ? 'rejected' : 'pending'))
                    <tr>
                        <td><strong>{{ $document->title }}</strong><br><span class="docs-note">{{ $document->original_name }} · {{ number_format($document->size_bytes / 1024, 1) }} KB</span></td>
                        <td>{{ \App\Support\StudentDocumentOptions::categoryLabel($document->category) }}</td>
                        <td>{{ $document->expiry_date ? \Illuminate\Support\Carbon::parse($document->expiry_date)->format('d M Y') : '-' }}@if($document->expiry_date && \Illuminate\Support\Carbon::parse($document->expiry_date)->isPast())<br><span class="ui-status status-rejected">{{ __('Expired') }}</span>@endif</td>
                        <td><span class="ui-status status-{{ $tone }}">{{ \App\Support\StudentDocumentOptions::statusLabel($document->status) }}</span></td>
                        <td>{{ $document->review_note ?: '-' }}@if($document->reviewer_name)<br><span class="docs-note">{{ $document->reviewer_name }}</span>@endif</td>
                        <td><a class="ui-btn" href="{{ route('student.documents.download', $document->id) }}">{{ __('Download') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">{{ __('No documents uploaded yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
    {{ $documents->links('vendor.pagination.studentedge') }}
</div>
@endsection
