@extends('layouts.app')

@section('title', __('Insurans Pelajar (Sem 3 & 5)'))



@section('header')
    <h2 class="ins-page-title">{{ __('Insurans Pelajar (Sem 3 & 5)') }}</h2>
@endsection

@section('content')
<div class="ins-shell">
    @if(session('success'))
        <div class="alert alert-success ins-alert">{{ session('success') }}</div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger ins-alert">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <header class="ins-hero">
        <div>
            <span class="ins-kicker">{{ __('PENGESAHAN & PEMATUHAN INSURANS') }}</span>
            <h1>{{ __('Rekod Bayaran Insurans Pelajar') }}</h1>
            <p>{{ __('Pemantauan resit bayaran insurans wajib bagi semua pelajar Semester 3 & Semester 5 Politeknik Besut.') }}</p>
        </div>
        <div class="ins-hero-actions">
            <a class="ins-btn" href="{{ route('admin.insurance.export', request()->query()) }}" title="{{ __('Eksport rekod ke format CSV / Excel') }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                {{ __('Eksport CSV / Excel') }}
            </a>
            <form method="POST" action="{{ route('admin.insurance.broadcast-notice') }}" onsubmit="return confirm('{{ __('Adakah anda pasti mahu menghantar notifikasi peringatan kepada semua pelajar Semester 3 & 5 yang belum membuat bayaran?') }}');">
                @csrf
                <button type="submit" class="ins-btn primary" title="{{ __('Hantar notifikasi push kepada pelajar yang belum selesai') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    {{ __('Hantar Notis Peringatan') }}
                </button>
            </form>
        </div>
    </header>

    <div class="ins-stats">
        <div class="ins-stat ins-stat-total">
            <span>{{ __('Jumlah Pelajar (Sem 3 & 5)') }}</span>
            <strong>{{ number_format($stats->total) }}</strong>
            <small>{{ __('Sem 3:') }} {{ $stats->sem3_total }} &middot; {{ __('Sem 5:') }} {{ $stats->sem5_total }}</small>
        </div>
        <div class="ins-stat ins-stat-approved">
            <span>{{ __('Telah Bayar & Disahkan') }}</span>
            <strong>{{ number_format($stats->approved) }}</strong>
            <small>{{ __('Kadar Pematuhan:') }} <strong>{{ $stats->rate }}%</strong></small>
        </div>
        <div class="ins-stat ins-stat-pending">
            <span>{{ __('Menunggu Pengesahan') }}</span>
            <strong>{{ number_format($stats->pending) }}</strong>
            <small>{{ __('Resit perlu disemak oleh admin') }}</small>
        </div>
        <div class="ins-stat ins-stat-unpaid">
            <span>{{ __('Belum Bayar / Tiada Resit') }}</span>
            <strong>{{ number_format($stats->unpaid + $stats->rejected) }}</strong>
            <small>{{ __('Ditolak:') }} {{ $stats->rejected }} &middot; {{ __('Belum muat naik:') }} {{ $stats->unpaid }}</small>
        </div>
    </div>

    <div class="ins-panel">
        <div class="ins-filter-bar">
            <div class="ins-tabs">
                <a href="{{ route('admin.insurance.index', array_merge(request()->query(), ['semester' => 'all'])) }}" class="ins-tab {{ ($filters['semester'] ?? 'all') === 'all' ? 'active' : '' }}">
                    {{ __('Semua (Sem 3 & 5)') }} ({{ $stats->total }})
                </a>
                <a href="{{ route('admin.insurance.index', array_merge(request()->query(), ['semester' => '3'])) }}" class="ins-tab {{ ($filters['semester'] ?? '') === '3' ? 'active' : '' }}">
                    {{ __('Semester 3 Sahaja') }} ({{ $stats->sem3_total }})
                </a>
                <a href="{{ route('admin.insurance.index', array_merge(request()->query(), ['semester' => '5'])) }}" class="ins-tab {{ ($filters['semester'] ?? '') === '5' ? 'active' : '' }}">
                    {{ __('Semester 5 Sahaja') }} ({{ $stats->sem5_total }})
                </a>
            </div>

            <form method="GET" action="{{ route('admin.insurance.index') }}" class="ins-filter-grid">
                <input type="hidden" name="semester" value="{{ $filters['semester'] ?? 'all' }}">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari nama pelajar, no. matrik, no. IC, kelas...') }}">
                <select name="status">
                    <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>{{ __('Semua Status Bayaran') }}</option>
                    <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>{{ __('Telah Disahkan (Approved)') }}</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Menunggu Semakan (Pending)') }}</option>
                    <option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>{{ __('Belum Muat Naik (Unpaid)') }}</option>
                    <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>{{ __('Resit Ditolak (Rejected)') }}</option>
                </select>
                <select name="program">
                    <option value="">{{ __('Semua Program') }}</option>
                    @foreach($programs as $p)
                        <option value="{{ $p }}" {{ ($filters['program'] ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ins-btn primary">{{ __('Tapis') }}</button>
            </form>
        </div>

        <div class="ins-table-wrap" data-lenis-prevent>
            <table class="ins-table">
                <thead>
                    <tr>
                        <th class="ins-col-no">#</th>
                        <th>{{ __('Pelajar') }}</th>
                        <th>{{ __('Semester') }}</th>
                        <th>{{ __('Status Bayaran') }}</th>
                        <th>{{ __('Resit Bayaran') }}</th>
                        <th>{{ __('Tarikh / Disemak Oleh') }}</th>
                        <th class="ins-col-actions">{{ __('Tindakan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $s)
                        <tr>
                            <td class="ins-row-no">
                                {{ $students->firstItem() + $index }}
                            </td>
                            <td>
                                <strong class="ins-student-name">{{ $s->full_name }}</strong>
                                <div class="ins-student-meta">
                                    {{ $s->matric_no }} &middot; {{ $s->ic_no }} &middot; <span>{{ $s->program }}</span> ({{ $s->class_name ?: '-' }})
                                </div>
                            </td>
                            <td>
                                <span class="ins-badge sem">Sem {{ $s->semester }}</span>
                            </td>
                            <td>
                                @if($s->doc_status === 'approved')
                                    <span class="ins-badge approved">✓ {{ __('Disahkan') }}</span>
                                @elseif($s->doc_status === 'pending')
                                    <span class="ins-badge pending">⏳ {{ __('Menunggu Semakan') }}</span>
                                @elseif($s->doc_status === 'rejected')
                                    <span class="ins-badge rejected">✕ {{ __('Ditolak') }}</span>
                                @else
                                    <span class="ins-badge unpaid">● {{ __('Belum Bayar') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($s->doc_id)
                                    <a href="{{ route('admin.insurance.download-receipt', $s->doc_id) }}" class="ins-btn ins-btn-compact" title="{{ __('Muat turun resit') }}">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        {{ Str::limit($s->doc_original_name, 22) }}
                                    </a>
                                    @if($s->doc_size_bytes)
                                        <div class="ins-file-size">
                                            {{ number_format($s->doc_size_bytes / 1024, 1) }} KB
                                        </div>
                                    @endif
                                @else
                                    <span class="ins-muted">{{ __('Tiada fail dimuat naik') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($s->doc_created_at)
                                    <div class="ins-review-date">
                                        {{ substr((string)$s->doc_created_at, 0, 10) }}
                                    </div>
                                @endif
                                @if($s->reviewer_name)
                                    <div class="ins-reviewer">
                                        {{ __('Oleh:') }} {{ $s->reviewer_name }}
                                    </div>
                                @endif
                                @if($s->doc_review_note)
                                    <div class="ins-review-note" title="{{ $s->doc_review_note }}">
                                        "{{ Str::limit($s->doc_review_note, 30) }}"
                                    </div>
                                @endif
                            </td>
                            <td class="ins-col-actions">
                                <div class="ins-actions">
                                    @if($s->doc_id && $s->doc_status === 'pending')
                                        <button type="button" class="ins-btn primary" onclick="openReviewModal({{ $s->doc_id }}, '{{ addslashes($s->full_name) }}', '{{ $s->matric_no }}', '{{ route('admin.insurance.download-receipt', $s->doc_id) }}')">
                                            {{ __('Semak Resit') }}
                                        </button>
                                    @elseif($s->doc_id)
                                        <a href="{{ route('admin.insurance.download-receipt', $s->doc_id) }}" class="ins-btn">
                                            {{ __('Muat Turun') }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ins-empty">
                                <strong>{{ __('Tiada rekod pelajar dijumpai.') }}</strong>
                                <p>{{ __('Cuba ubah kata kunci carian atau tetapan penapis.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="ins-pagination">
                {{ $students->onEachSide(1)->links('vendor.pagination.myhep') }}
            </div>
        @endif
    </div>
</div>

<!-- Review Modal -->
<div class="ins-modal" id="reviewModal">
    <div class="ins-modal-card">
        <div class="ins-modal-head">
            <strong>{{ __('Semakan Resit Bayaran Insurans') }}</strong>
            <button type="button" class="ins-modal-close" onclick="closeReviewModal()">&times;</button>
        </div>
        <form method="POST" id="reviewForm" action="">
            @csrf
            @method('PATCH')
            <div class="ins-modal-body">
                <div style="padding:12px 14px;background:#faf6f1;border:1px solid var(--border,#eadac8);border-radius:10px;margin-bottom:14px;">
                    <strong id="modalStudentName" style="display:block;font-size:0.95rem;color:var(--text,#241d16);"></strong>
                    <span id="modalMatricNo" style="font-size:0.8rem;color:var(--text-muted,#746b62);"></span>
                    <div style="margin-top:8px;">
                        <a id="modalDownloadLink" href="#" target="_blank" class="ins-btn" style="font-size:0.75rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            {{ __('Buka / Pratonton Fail Resit') }}
                        </a>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label for="reviewNote" style="font-size:0.8rem;font-weight:750;display:block;margin-bottom:5px;color:var(--text,#241d16);">
                        {{ __('Catatan Semakan (Wajib jika ditolak)') }}
                    </label>
                    <textarea name="review_note" id="reviewNote" rows="3" style="width:100%;padding:8px 10px;border:1px solid var(--border,#eadac8);border-radius:8px;font-size:0.88rem;box-sizing:border-box;" placeholder="{{ __('Contoh: Jumlah bayaran tidak mencukupi / resit kabur...') }}"></textarea>
                </div>

                <input type="hidden" name="status" id="modalStatusInput" value="approved">

                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="ins-btn" onclick="closeReviewModal()">{{ __('Batal') }}</button>
                    <button type="submit" class="ins-btn danger" onclick="document.getElementById('modalStatusInput').value='rejected'">
                        {{ __('✕ Tolak Resit') }}
                    </button>
                    <button type="submit" class="ins-btn success" onclick="document.getElementById('modalStatusInput').value='approved'">
                        {{ __('✓ Sahkan Bayaran') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openReviewModal(docId, studentName, matricNo, downloadUrl) {
    const modal = document.getElementById('reviewModal');
    const form = document.getElementById('reviewForm');
    const nameEl = document.getElementById('modalStudentName');
    const matricEl = document.getElementById('modalMatricNo');
    const linkEl = document.getElementById('modalDownloadLink');

    form.action = '{{ url("/admin/insurance/receipt") }}/' + docId + '/review';
    nameEl.textContent = studentName;
    matricEl.textContent = matricNo;
    linkEl.href = downloadUrl;
    document.getElementById('reviewNote').value = '';

    modal.classList.add('open');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('open');
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('reviewModal');
    if (e.target === modal) {
        closeReviewModal();
    }
});
</script>
@endsection
