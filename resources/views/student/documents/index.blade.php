@extends('layouts.app')

@section('title', __('Document Centre'))



@section('header')
    <h2>{{ __('Document Centre') }}</h2>
@endsection

@section('content')
<div class="docs-shell">
    @if(session('success'))<div class="se-feedback se-feedback--success">{{ session('success') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="se-feedback se-feedback--error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    @if(!empty($isSem3Or5))
        <section class="ui-card" style="padding:16px 20px;border-radius:16px;border:1px solid @if(!$insuranceDoc || $insuranceDoc->status === 'rejected') #fca5a5;background:#fff5f5; @elseif($insuranceDoc->status === 'pending') #fef08a;background:#fffbeb; @else #bbf7d0;background:#f0fdf4; @endif">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:@if(!$insuranceDoc || $insuranceDoc->status === 'rejected') #fee2e2;color:#b91c1c; @elseif($insuranceDoc->status === 'pending') #fef9c3;color:#854d0e; @else #dcfce7;color:#166534; @endif flex-shrink:0;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    </div>
                    <div>
                        <strong style="font-size:0.95rem;color:@if(!$insuranceDoc || $insuranceDoc->status === 'rejected') #991b1b; @elseif($insuranceDoc->status === 'pending') #854d0e; @else #166534; @endif">
                            {{ __('Tindakan Wajib: Bayaran Insurans Pelajar (Semester :sem)', ['sem' => $student->semester ?? '3/5']) }}
                        </strong>
                        <p style="margin:2px 0 0;font-size:0.82rem;color:@if(!$insuranceDoc || $insuranceDoc->status === 'rejected') #7f1d1d; @elseif($insuranceDoc->status === 'pending') #713f12; @else #14532d; @endif">
                            @if(!$insuranceDoc)
                                {{ __('Semua pelajar Semester 3 & 5 diwajibkan memuat naik resit bayaran insurans bagi sesi akademik ini.') }}
                            @elseif($insuranceDoc->status === 'pending')
                                {{ __('Resit bayaran insurans anda (:name) sedang disemak oleh pihak pengurusan HEP / Disiplin.', ['name' => $insuranceDoc->original_name]) }}
                            @elseif($insuranceDoc->status === 'rejected')
                                {{ __('Resit bayaran insurans anda telah ditolak. Sebab: :reason. Sila muat naik semula resit yang sah.', ['reason' => $insuranceDoc->review_note ?: __('Sila pastikan maklumat resit lengkap dan jelas.')]) }}
                            @else
                                {{ __('Resit bayaran insurans anda telah disahkan dan diluluskan. Terima kasih.') }}
                            @endif
                        </p>
                    </div>
                </div>
                <div>
                    @if(!$insuranceDoc || $insuranceDoc->status === 'rejected')
                        <button type="button" class="ui-btn primary" onclick="document.getElementById('document-category').value='insurance_payment';document.getElementById('document-title').value='{{ __('Resit Bayaran Insurans Semester :sem', ['sem' => $student->semester ?? '']) }}';document.getElementById('document-file').focus();">
                            {{ __('Muat Naik Resit Insurans') }}
                        </button>
                    @elseif($insuranceDoc->status === 'pending')
                        <span class="ui-badge" style="background:#fef9c3;color:#854d0e;border:1px solid #fef08a;font-weight:800;padding:6px 12px;border-radius:999px;">
                            ⏳ {{ __('Menunggu Semakan') }}
                        </span>
                    @else
                        <span class="ui-badge" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;font-weight:800;padding:6px 12px;border-radius:999px;">
                            ✓ {{ __('Disahkan') }}
                        </span>
                    @endif
                </div>
            </div>
        </section>
    @endif

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
