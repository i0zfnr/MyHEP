@extends('layouts.app')

@section('title', 'Edit Rekod Scholarship')



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Edit Rekod Scholarship') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.scholarships.update', $record->id) }}">
        @csrf
        @method('PUT')
        <div class="card">
            <h2>{{ __('Maklumat Rekod') }}</h2>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="student_search">{{ __('Cari Pelajar') }}</label>
                        <input id="student_search" type="text" placeholder="{{ __('Taip nama atau nombor matrik') }}">
                        <div style="margin-top:6px;font-size:12px;color:#7a6555;">{{ __('Taip sekurang-kurangnya 2 huruf untuk cari pelajar melalui AJAX.') }}</div>
                    </div>
                    <div>
                        <label for="student_id">{{ __('Pelajar') }}</label>
                        <select id="student_id" name="student_id" required>
                            <option value="">{{ __('Pilih pelajar') }}</option>
                            @if($selectedStudent)
                                <option value="{{ $selectedStudent->id }}" selected>{{ $selectedStudent->full_name }} ({{ $selectedStudent->matric_no }})</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="provider_name">{{ __('Penyedia') }}</label>
                        <input id="provider_name" type="text" name="provider_name" value="{{ old('provider_name', $record->provider_name) }}" placeholder="{{ __('Contoh: JPA / MARA') }}">
                    </div>
                    <div></div>
                </div>

                <div class="grid grid-3" style="margin-top:12px;">
                    <div>
                        <label for="type">{{ __('Jenis') }}</label>
                        <select id="type" name="type" required>
                            @foreach(['scholarship','welfare','sponsorship','none'] as $type)
                                <option value="{{ $type }}" {{ old('type', $record->type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="amount">Jumlah (RM)</label>
                        <input id="amount" type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $record->amount) }}">
                    </div>
                    <div>
                        <label for="status">{{ __('Status') }}</label>
                        <select id="status" name="status" required>
                            @foreach(['pending','confirmed','rejected'] as $status)
                                <option value="{{ $status }}" {{ old('status', $record->status) === $status ? 'selected' : '' }}>{{ __($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <label for="proof_file">Path Fail Bukti (optional)</label>
                    <input id="proof_file" type="text" name="proof_file" value="{{ old('proof_file', $record->proof_file) }}" placeholder="{{ __('Contoh: uploads/proof/file.pdf') }}">
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Perubahan') }}</button>
            <a class="btn" href="{{ route('admin.scholarships.index') }}">{{ __('Batal') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const studentSearch = document.getElementById('student_search');
    const studentSelect = document.getElementById('student_id');

    if (studentSearch && studentSelect) {
        let studentSearchTimer = null;

        studentSearch.addEventListener('input', () => {
            const q = studentSearch.value.trim();
            if (studentSearchTimer) clearTimeout(studentSearchTimer);

            if (q.length < 2) return;

            studentSearchTimer = setTimeout(async () => {
                try {
                    const resp = await fetch(`{{ route('admin.students.search') }}?q=${encodeURIComponent(q)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const payload = await resp.json().catch(() => ({ data: [] }));
                    if (!resp.ok || !Array.isArray(payload.data)) return;

                    const current = studentSelect.value;
                    studentSelect.innerHTML = '<option value="">' + @json(__('Pilih pelajar')) + '</option>';
                    payload.data.forEach((s) => {
                        const opt = document.createElement('option');
                        opt.value = String(s.id);
                        opt.textContent = `${s.full_name} (${s.matric_no})`;
                        if (String(s.id) === current) opt.selected = true;
                        studentSelect.appendChild(opt);
                    });

                    if (!studentSelect.value && payload.data.length === 1) {
                        studentSelect.value = String(payload.data[0].id);
                    }
                } catch (e) {
                    // silent fallback to manual selection
                }
            }, 320);
        });
    }
</script>
@endpush


