@extends('layouts.app')

@section('title', __('Borang Biasiswa & Bantuan Kebajikan'))

@push('styles')
<style>
    .wrap { max-width: 920px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:14px; overflow:hidden; }
    .head { padding:14px 18px; border-bottom:1px solid #ede4d9; background:linear-gradient(180deg,#fff 0%,#fbf4ec 100%); position:relative; }
    .head::before { content:''; position:absolute; left:0; top:0; bottom:0; width:4px; background:linear-gradient(180deg,#8f6f52,#c7a98b); }
    .body { padding:18px; }
    .grid { display:grid; grid-template-columns:1fr; gap:12px; }
    @media (min-width: 900px) { .grid-2 { grid-template-columns:1fr 1fr; } .grid-3 { grid-template-columns:1fr 1fr 1fr; } }
    label { display:block; margin-bottom:6px; font-size:13px; font-weight:700; color:#5c4738; }
    label span.req { color:#dc2626; margin-left:2px; }
    input, select, textarea { width:100%; border:1px solid #e5d8c8; border-radius:10px; padding:10px 12px; font-size:14px; background:#fff; box-sizing:border-box; }
    input[readonly] { background:#faf7f4; color:#7a6555; cursor:not-allowed; }
    .actions { display:flex; gap:10px; margin-top:16px; flex-wrap:wrap; }
    .btn { display:inline-flex; align-items:center; gap:6px; border:1px solid #cbb9a4; background:#fff; color:#685141; border-radius:10px; padding:10px 16px; text-decoration:none; font-weight:700; font-size:14px; cursor:pointer; transition:all .15s ease; }
    .btn:hover { background:#f9f2ea; transform:translateY(-1px); border-color:#bb9c7d; }
    .btn-primary { background:linear-gradient(135deg,#7b5b43 0%,#b69172 100%); color:#fff; border:none; }
    .btn-primary:hover { box-shadow:0 8px 18px rgba(97,73,52,.2); background:linear-gradient(135deg,#6d4f38 0%,#a88263 100%); }
    .hint { margin:4px 0 0; color:#7a6555; font-size:12px; line-height:1.45; }
    .err { margin-bottom:14px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:10px; padding:12px; font-size:13px; }
    
    /* Type Selector Cards */
    .type-cards { display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px; margin-top:8px; }
    .type-card {
        border:1.5px solid #e5d8c8; border-radius:12px; padding:14px; cursor:pointer;
        background:#fffdfa; transition:all .2s ease; display:flex; flex-direction:column; gap:8px;
    }
    .type-card:hover { border-color:#c7a98b; transform:translateY(-2px); box-shadow:0 8px 18px rgba(61,46,34,.08); }
    .type-card input[type="radio"] { width:auto; margin:0 6px 0 0; accent-color:#7b5b43; }
    .type-card.selected { border-color:#7b5b43; background:linear-gradient(180deg,#fffdfa 0%,#fbf4ec 100%); box-shadow:0 0 0 1px #7b5b43; }
    .type-card-title { font-weight:800; font-size:14px; color:#2d1f14; display:flex; align-items:center; gap:8px; }
    .type-card-desc { font-size:12px; color:#7a6555; line-height:1.45; }

    .type-icon-box {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .type-icon-box.scholarship {
        background: rgba(2, 132, 199, 0.12);
        color: #0284c7;
    }
    .type-icon-box.welfare {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
    }
    .type-icon-box.none {
        background: rgba(115, 115, 115, 0.12);
        color: #737373;
    }

    .section-divider {
        margin:20px 0 16px; padding-top:16px; border-top:1px dashed #e8d9cb;
        display:flex; align-items:center; gap:8px; font-weight:800; font-size:14px; color:#685141;
    }
    .section-divider svg { width:18px; height:18px; color:#7b5b43; }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Borang Biasiswa & Bantuan Kebajikan') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())
        <div class="err">
            <strong>{{ __('Sila semak maklumat yang dimasukkan:') }}</strong>
            <ul style="margin:6px 0 0; padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('student.scholarship-status.submit') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="card">
            <div class="head">
                <strong style="font-size:15px; color:#2d221a;">{{ __('Maklumat Pengesahan Biasiswa & Permohonan Kebajikan') }}</strong>
            </div>
            <div class="body">
                <p class="hint" style="margin-bottom:14px;">
                    {{ __('Pilih status anda sama ada menerima biasiswa/penajaan luar, ingin memohon bantuan kebajikan khas Politeknik Besut, atau tiada biasiswa.') }}
                </p>

                <!-- Student Info (Read-only) -->
                <div class="grid grid-3">
                    <div>
                        <label>{{ __('Nama Penuh') }}</label>
                        <input type="text" value="{{ $student->full_name }}" readonly>
                    </div>
                    <div>
                        <label>{{ __('No. Matrik') }}</label>
                        <input type="text" value="{{ $student->matric_no }}" readonly>
                    </div>
                    <div>
                        <label>{{ __('Program Pengajian') }}</label>
                        <input type="text" value="{{ $student->program }}" readonly>
                    </div>
                </div>

                <!-- Choose Application Type -->
                <div style="margin-top:18px;">
                    <label>{{ __('Pilihan Status / Permohonan') }} <span class="req">*</span></label>
                    @php
                        $currentType = old('application_type', $submission->application_type ?? ($submission && $submission->has_scholarship === 'yes' ? 'scholarship' : ($submission && $submission->has_scholarship === 'no' ? 'none' : 'none')));
                    @endphp

                    <div class="type-cards">
                        <label class="type-card @if($currentType === 'scholarship') selected @endif" id="card_scholarship">
                            <div class="type-card-title">
                                <input type="radio" name="application_type" value="scholarship" required @checked($currentType === 'scholarship')>
                                <span class="type-icon-box scholarship">
                                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                </span>
                                <span>{{ __('Biasiswa / Penajaan') }}</span>
                            </div>
                            <span class="type-card-desc">{{ __('Saya sedang menerima biasiswa/tajaan luar (JPA, MARA, Yayasan, Zakat dll).') }}</span>
                        </label>

                        <label class="type-card @if($currentType === 'welfare') selected @endif" id="card_welfare">
                            <div class="type-card-title">
                                <input type="radio" name="application_type" value="welfare" required @checked($currentType === 'welfare')>
                                <span class="type-icon-box welfare">
                                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </span>
                                <span>{{ __('Bantuan Kebajikan') }}</span>
                            </div>
                            <span class="type-card-desc">{{ __('Permohonan bantuan kebajikan pelajar, bencana, kematian waris, atau sara hidup B40.') }}</span>
                        </label>

                        <label class="type-card @if($currentType === 'none') selected @endif" id="card_none">
                            <div class="type-card-title">
                                <input type="radio" name="application_type" value="none" required @checked($currentType === 'none')>
                                <span class="type-icon-box none">
                                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                </span>
                                <span>{{ __('Tiada Biasiswa / Bantuan') }}</span>
                            </div>
                            <span class="type-card-desc">{{ __('Saya tidak menerima sebarang biasiswa dan tidak memohon bantuan kebajikan.') }}</span>
                        </label>
                    </div>
                </div>

                <!-- SECTION 1: SCHOLARSHIP DETAILS -->
                <div id="section_scholarship" style="display:none; margin-top:20px;">
                    <div class="section-divider">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <span>{{ __('Maklumat Penajaan Biasiswa') }}</span>
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <label for="sponsor_name">{{ __('Nama Penaja Biasiswa') }} <span class="req">*</span></label>
                            <input id="sponsor_name" type="text" name="sponsor_name" value="{{ old('sponsor_name', $submission->sponsor_name ?? '') }}" placeholder="{{ __('Contoh: JPA / MARA / Yayasan Terengganu / Zakat') }}">
                        </div>
                        <div>
                            <label for="monthly_amount">{{ __('Jumlah Elaun / Tajaan Sebulan (RM)') }} <span class="req">*</span></label>
                            <input id="monthly_amount" type="number" step="0.01" min="0" name="monthly_amount" value="{{ old('monthly_amount', $submission->monthly_amount ?? '') }}" placeholder="{{ __('Contoh: 500.00') }}">
                        </div>
                    </div>

                    <div style="margin-top:12px;">
                        <label for="offer_letter">{{ __('Surat Tawaran Biasiswa (PDF / Imej)') }} @if(!$document)<span class="req">*</span>@endif</label>
                        <input id="offer_letter" type="file" name="offer_letter" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
                        <p class="hint">{{ __('Format diterima: PDF, JPG, PNG sehingga 10 MB. Dokumen ini wajib untuk pengesahan.') }}</p>
                        @if($document && ($submission->application_type ?? '') === 'scholarship')
                            <div style="margin-top:8px;">
                                <a class="btn" href="{{ route('student.documents.download', $document->id) }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    {{ __('Muat Turun Dokumen Semasa:') }} {{ $document->original_name }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- SECTION 2: WELFARE DETAILS -->
                <div id="section_welfare" style="display:none; margin-top:20px;">
                    <div class="section-divider">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>{{ __('Maklumat Waris & Keluarga') }}</span>
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <label for="guardian_name">{{ __('Nama Penjaga / Waris') }} <span class="req">*</span></label>
                            <input id="guardian_name" type="text" name="guardian_name" value="{{ old('guardian_name', $submission->guardian_name ?? ($student->guardian_name ?? '')) }}" placeholder="{{ __('Nama penuh bapa/ibu/penjaga') }}">
                        </div>
                        <div>
                            <label for="guardian_ic_no">{{ __('No. Kad Pengenalan Penjaga') }} <span class="req">*</span></label>
                            <input id="guardian_ic_no" type="text" name="guardian_ic_no" value="{{ old('guardian_ic_no', $submission->guardian_ic_no ?? ($student->guardian_ic_no ?? '')) }}" placeholder="{{ __('Contoh: 750101035555') }}">
                        </div>
                    </div>

                    <div class="grid grid-3" style="margin-top:12px;">
                        <div>
                            <label for="guardian_relationship">{{ __('Hubungan Waris') }} <span class="req">*</span></label>
                            <select id="guardian_relationship" name="guardian_relationship">
                                @php
                                    $rel = old('guardian_relationship', $submission->guardian_relationship ?? 'Bapa');
                                @endphp
                                <option value="Bapa" @selected($rel === 'Bapa')>{{ __('Bapa') }}</option>
                                <option value="Ibu" @selected($rel === 'Ibu')>{{ __('Ibu') }}</option>
                                <option value="Penjaga / Waris Sah" @selected($rel === 'Penjaga / Waris Sah')>{{ __('Penjaga / Waris Sah') }}</option>
                                <option value="Abang / Kakak" @selected($rel === 'Abang / Kakak')>{{ __('Abang / Kakak') }}</option>
                                <option value="Datuk / Nenek" @selected($rel === 'Datuk / Nenek')>{{ __('Datuk / Nenek') }}</option>
                                <option value="Lain-lain" @selected($rel === 'Lain-lain')>{{ __('Lain-lain') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="guardian_phone">{{ __('No. Telefon Penjaga') }} <span class="req">*</span></label>
                            <input id="guardian_phone" type="text" name="guardian_phone" value="{{ old('guardian_phone', $submission->guardian_phone ?? ($student->guardian_phone ?? '')) }}" placeholder="{{ __('Contoh: 0123456789') }}">
                        </div>
                        <div>
                            <label for="guardian_occupation">{{ __('Pekerjaan Penjaga') }} <span class="req">*</span></label>
                            <input id="guardian_occupation" type="text" name="guardian_occupation" value="{{ old('guardian_occupation', $submission->guardian_occupation ?? ($student->guardian_occupation ?? '')) }}" placeholder="{{ __('Contoh: Buruh / Petani / Bekerja Sendiri') }}">
                        </div>
                    </div>

                    <div class="grid grid-2" style="margin-top:12px;">
                        <div>
                            <label for="family_income">{{ __('Pendapatan Bulanan Keluarga (RM)') }} <span class="req">*</span></label>
                            <input id="family_income" type="number" step="0.01" min="0" name="family_income" value="{{ old('family_income', $submission->family_income ?? ($student->family_income ?? '')) }}" placeholder="{{ __('Contoh: 1200.00') }}">
                            <small class="hint">{{ __('Jumlah pendapatan isi rumah ibu bapa / penjaga sebulan.') }}</small>
                        </div>
                        <div>
                            <label for="dependents_count">{{ __('Bilangan Tanggungan Keluarga') }}</label>
                            <input id="dependents_count" type="number" min="0" max="30" name="dependents_count" value="{{ old('dependents_count', $submission->dependents_count ?? 1) }}" placeholder="{{ __('Contoh: 4') }}">
                            <small class="hint">{{ __('Termasuk anak/adik beradik yang masih belajar.') }}</small>
                        </div>
                    </div>

                    <div class="section-divider">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>{{ __('Keterangan Permohonan Bantuan Kebajikan') }}</span>
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <label for="welfare_category">{{ __('Kategori Bantuan Kebajikan') }} <span class="req">*</span></label>
                            <select id="welfare_category" name="welfare_category">
                                @php
                                    $wCat = old('welfare_category', $submission->welfare_category ?? 'Bantuan Miskin Tegar / B40');
                                @endphp
                                <option value="Bantuan Miskin Tegar / B40" @selected($wCat === 'Bantuan Miskin Tegar / B40')>{{ __('Bantuan Miskin Tegar / B40') }}</option>
                                <option value="Bantuan Bencana Alam (Banjir / Ribut / Kebakaran)" @selected($wCat === 'Bantuan Bencana Alam (Banjir / Ribut / Kebakaran)')>{{ __('Bantuan Bencana Alam (Banjir / Ribut / Kebakaran)') }}</option>
                                <option value="Bantuan Kematian Waris / Ibu Bapa" @selected($wCat === 'Bantuan Kematian Waris / Ibu Bapa')>{{ __('Bantuan Kematian Waris / Ibu Bapa') }}</option>
                                <option value="Bantuan Rawatan Perubatan / Sakit Kronik" @selected($wCat === 'Bantuan Rawatan Perubatan / Sakit Kronik')>{{ __('Bantuan Rawatan Perubatan / Sakit Kronik') }}</option>
                                <option value="Bantuan Keperluan Pengajian Khas / Sara Hidup" @selected($wCat === 'Bantuan Keperluan Pengajian Khas / Sara Hidup')>{{ __('Bantuan Keperluan Pengajian Khas / Sara Hidup') }}</option>
                                <option value="Lain-lain Bantuan Kebajikan" @selected($wCat === 'Lain-lain Bantuan Kebajikan')>{{ __('Lain-lain Bantuan Kebajikan') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="welfare_amount">{{ __('Anggaran Bantuan Diperlukan (RM) — Pilihan') }}</label>
                            <input id="welfare_amount" type="number" step="0.01" min="0" name="welfare_amount" value="{{ old('welfare_amount', $submission->welfare_amount ?? '') }}" placeholder="{{ __('Contoh: 300.00 (atau biarkan kosong)') }}">
                        </div>
                    </div>

                    <div style="margin-top:12px;">
                        <label for="welfare_description">{{ __('Keterangan Masalah / Justifikasi Permohonan') }} <span class="req">*</span></label>
                        <textarea id="welfare_description" name="welfare_description" rows="3" placeholder="{{ __('Sila jelaskan situasi keluarga, masalah kewangan, atau bencana yang dihadapi...') }}">{{ old('welfare_description', $submission->welfare_description ?? '') }}</textarea>
                    </div>

                    <div style="margin-top:12px;">
                        <label for="welfare_proof">{{ __('Dokumen Bukti Kebajikan (PDF / Imej)') }} @if(!$document)<span class="req">*</span>@endif</label>
                        <input id="welfare_proof" type="file" name="welfare_proof" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
                        <p class="hint">{{ __('Contoh dokumen: Slip Gaji Ibu Bapa / Surat Pengesahan Pendapatan Penghulu / Sijil Kematian / Laporan Polis / Surat Hospital (Maksimum 10 MB).') }}</p>
                        @if($document && ($submission->application_type ?? '') === 'welfare')
                            <div style="margin-top:8px;">
                                <a class="btn" href="{{ route('student.documents.download', $document->id) }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    {{ __('Muat Turun Dokumen Semasa:') }} {{ $document->original_name }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Common Notes Field -->
                <div style="margin-top:18px;">
                    <label for="notes">{{ __('Catatan Tambahan (Pilihan)') }}</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="{{ __('Sebarang maklumat tambahan untuk perhatian pihak JHEP...') }}">{{ old('notes', $submission->notes ?? '') }}</textarea>
                </div>

                <div class="actions">
                    <button class="btn btn-primary" type="submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ __('Hantar Permohonan / Kemaskini') }}
                    </button>
                    <a class="btn" href="{{ route('student.scholarships.index') }}">{{ __('Kembali') }}</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radioScholarship = document.querySelector('input[name="application_type"][value="scholarship"]');
    const radioWelfare = document.querySelector('input[name="application_type"][value="welfare"]');
    const radioNone = document.querySelector('input[name="application_type"][value="none"]');

    const cardScholarship = document.getElementById('card_scholarship');
    const cardWelfare = document.getElementById('card_welfare');
    const cardNone = document.getElementById('card_none');

    const secScholarship = document.getElementById('section_scholarship');
    const secWelfare = document.getElementById('section_welfare');

    const sponsorInput = document.getElementById('sponsor_name');
    const amountInput = document.getElementById('monthly_amount');
    const offerFileInput = document.getElementById('offer_letter');

    const guardianName = document.getElementById('guardian_name');
    const guardianIc = document.getElementById('guardian_ic_no');
    const guardianPhone = document.getElementById('guardian_phone');
    const guardianOcc = document.getElementById('guardian_occupation');
    const famIncome = document.getElementById('family_income');
    const welfareDesc = document.getElementById('welfare_description');
    const welfareFileInput = document.getElementById('welfare_proof');

    const hasExistingDoc = @json((bool) $document);

    function syncType() {
        const type = document.querySelector('input[name="application_type"]:checked')?.value || 'none';

        // Cards styling
        cardScholarship.classList.toggle('selected', type === 'scholarship');
        cardWelfare.classList.toggle('selected', type === 'welfare');
        cardNone.classList.toggle('selected', type === 'none');

        // Section visibility
        secScholarship.style.display = (type === 'scholarship') ? 'block' : 'none';
        secWelfare.style.display = (type === 'welfare') ? 'block' : 'none';

        // Scholarship requirements
        const isSch = (type === 'scholarship');
        if (sponsorInput) sponsorInput.required = isSch;
        if (amountInput) amountInput.required = isSch;
        if (offerFileInput) offerFileInput.required = isSch && !hasExistingDoc;

        // Welfare requirements
        const isWel = (type === 'welfare');
        if (guardianName) guardianName.required = isWel;
        if (guardianIc) guardianIc.required = isWel;
        if (guardianPhone) guardianPhone.required = isWel;
        if (guardianOcc) guardianOcc.required = isWel;
        if (famIncome) famIncome.required = isWel;
        if (welfareDesc) welfareDesc.required = isWel;
        if (welfareFileInput) welfareFileInput.required = isWel && !hasExistingDoc;
    }

    document.querySelectorAll('input[name="application_type"]').forEach(function (radio) {
        radio.addEventListener('change', syncType);
    });

    syncType();
});
</script>
@endpush
