@extends('layouts.app')

@section('title', __('Student Documents'))



@section('header')<h2>{{ __('Student Documents') }}</h2>@endsection

@section('content')
<div class="admin-docs">
    @if(session('success'))<div class="se-feedback se-feedback--success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="se-feedback se-feedback--error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    @if($limitedToInsurancePayments ?? false)
        <div class="se-feedback se-feedback--info">{{ __('Discipline Administration can view and review only student insurance-payment documents. Other document categories remain private to their authorized reviewers.') }}</div>
    @endif

    <div class="admin-doc-stats">
        <div class="admin-doc-stat"><span>{{ __('Total') }}</span><strong>{{ (int) ($counts->total ?? 0) }}</strong></div>
        <div class="admin-doc-stat"><span>{{ __('Pending') }}</span><strong>{{ (int) ($counts->pending ?? 0) }}</strong></div>
        <div class="admin-doc-stat"><span>{{ __('Approved') }}</span><strong>{{ (int) ($counts->approved ?? 0) }}</strong></div>
        <div class="admin-doc-stat"><span>{{ __('Rejected') }}</span><strong>{{ (int) ($counts->rejected ?? 0) }}</strong></div>
    </div>

    <section class="ui-card"><div class="ui-card-body"><form method="GET" action="{{ route('admin.documents.index') }}" class="admin-doc-filter">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Search student or document') }}">
        <select name="category"><option value="">{{ __('All Categories') }}</option>@foreach($categories as $value => $label)<option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ __($label) }}</option>@endforeach</select>
        <select name="status"><option value="">{{ __('All Statuses') }}</option>@foreach(\App\Support\StudentDocumentOptions::STATUSES as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ __($label) }}</option>@endforeach</select>
        <select name="expiry"><option value="">{{ __('Any Expiry') }}</option><option value="no_expiry" @selected(($filters['expiry'] ?? '') === 'no_expiry')>{{ __('No Expiry') }}</option><option value="valid" @selected(($filters['expiry'] ?? '') === 'valid')>{{ __('Valid') }}</option><option value="expiring_30" @selected(($filters['expiry'] ?? '') === 'expiring_30')>{{ __('Expiring Soon') }}</option><option value="expired" @selected(($filters['expiry'] ?? '') === 'expired')>{{ __('Expired') }}</option></select>
        <button class="ui-btn" type="submit">{{ __('Filter') }}</button>
    </form></div></section>

    <section class="ui-card"><p class="admin-doc-scroll-hint">{{ __('Swipe horizontally to view all document columns.') }}</p><div class="admin-doc-table-wrap"><table class="ui-table">
        <thead><tr><th>{{ __('Student') }}</th><th>{{ __('Document') }}</th><th>{{ __('Category / Expiry') }}</th><th>{{ __('Status') }}</th><th>{{ __('Review') }}</th></tr></thead>
        <tbody>
        @forelse($documents as $document)
            @php($tone = $document->status === 'approved' ? 'confirmed' : ($document->status === 'rejected' ? 'rejected' : 'pending'))
            <tr>
                <td><strong>{{ $document->student_name }}</strong><br><span class="admin-doc-meta">{{ $document->matric_no ?: '-' }}</span></td>
                <td><strong>{{ $document->title }}</strong><br><span class="admin-doc-meta">{{ $document->original_name }} · {{ number_format($document->size_bytes / 1024, 1) }} KB</span><br><a class="ui-btn" href="{{ route('admin.documents.download', $document->id) }}">{{ __('Download') }}</a></td>
                <td>{{ \App\Support\StudentDocumentOptions::categoryLabel($document->category) }}<br><span class="admin-doc-meta">{{ $document->expiry_date ? \Illuminate\Support\Carbon::parse($document->expiry_date)->format('d M Y') : __('No Expiry') }}</span></td>
                <td><span class="ui-status status-{{ $tone }}">{{ \App\Support\StudentDocumentOptions::statusLabel($document->status) }}</span>@if($document->reviewer_name)<br><span class="admin-doc-meta">{{ $document->reviewer_name }} · {{ \Illuminate\Support\Carbon::parse($document->reviewed_at)->diffForHumans() }}</span>@endif</td>
                <td>
                    @if($document->status === 'pending')
                        <form method="POST" action="{{ route('admin.documents.review', $document->id) }}" class="admin-doc-review">@csrf @method('PATCH')<input type="hidden" name="status" value=""><textarea name="review_note" rows="2" maxlength="1000" placeholder="{{ __('Review note (required for rejection)') }}"></textarea><div class="admin-doc-review-actions"><button class="ui-btn primary" name="status" value="approved" type="submit" onclick="var h=this.form.querySelector('input[name=status]');if(h)h.value='approved'">{{ __('Approve') }}</button><button class="ui-btn btn-danger" name="status" value="rejected" type="submit" onclick="var h=this.form.querySelector('input[name=status]');if(h)h.value='rejected'">{{ __('Reject') }}</button></div></form>
                    @else
                        {{ $document->review_note ?: '-' }}
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;color:var(--text-muted);">{{ __('No documents found.') }}</td></tr>
        @endforelse
        </tbody>
    </table></div></section>
    {{ $documents->links('vendor.pagination.myhep') }}
</div>
@endsection
