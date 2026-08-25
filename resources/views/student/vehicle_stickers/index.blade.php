@extends('layouts.app')

@section('title', __('Permohonan Sticker Kenderaan'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Sticker Kenderaan') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="quick">
        <a class="btn" href="{{ route('student.dashboard') }}">{{ __('Kembali ke Index') }}</a>
        <a class="btn" href="{{ route('student.offenses.index') }}">{{ __('Mohon Bayaran Denda') }}</a>
        <a class="btn" href="{{ route('student.rules.index') }}">{{ __('Lihat Peraturan') }}</a>
        <a class="btn" href="{{ route('student.scholarships.index') }}">{{ __('Portal Scholarship') }}</a>
    </div>

    <div class="card">
        <div class="head"><strong>{{ __('Permohonan Baru') }}</strong></div>
        <div class="body">
            <form method="POST" action="{{ route('student.vehicle-stickers.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid">
                    <div>
                        <label for="vehicle_no">{{ __('No. Kenderaan') }}</label>
                        <input id="vehicle_no" type="text" name="vehicle_no" value="{{ old('vehicle_no') }}" placeholder="{{ __('Contoh:') }} TBA1234" required>
                    </div>
                    <div>
                        <label for="vehicle_type">{{ __('Jenis Kenderaan') }}</label>
                        <select id="vehicle_type" name="vehicle_type" required>
                            <option value="">{{ __('Pilih jenis') }}</option>
                            @foreach(['Motosikal', 'Kereta', 'Lain-lain'] as $type)
                                <option value="{{ $type }}" {{ old('vehicle_type') === $type ? 'selected' : '' }}>{{ __($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex; align-items:end;">
                        <button class="btn btn-primary" type="submit">{{ __('Hantar Permohonan') }}</button>
                    </div>
                </div>
                <div class="doc-grid">
                    <div>
                        <label for="license_card_image">{{ __('Gambar Kad Lesen') }}</label>
                        <input id="license_card_image" type="file" name="license_card_image" accept="image/jpeg,image/png,image/webp" required>
                    </div>
                    <div>
                        <label for="parent_permission_image">{{ __('Surat Kebenaran Ibu Bapa') }}</label>
                        <input id="parent_permission_image" type="file" name="parent_permission_image" accept="image/jpeg,image/png,image/webp" required>
                    </div>
                    <div>
                        <label for="vehicle_plate_image">{{ __('Gambar Kenderaan (Nombor Plat Jelas)') }}</label>
                        <input id="vehicle_plate_image" type="file" name="vehicle_plate_image" accept="image/jpeg,image/png,image/webp" required>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="head"><strong>{{ __('Rekod Permohonan Saya') }}</strong></div>
        <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>{{ __('No. Kenderaan') }}</th><th>{{ __('Jenis') }}</th><th>{{ __('Dokumen') }}</th><th>{{ __('Status') }}</th><th>{{ __('Disemak Oleh') }}</th><th>{{ __('Tarikh') }}</th></tr></thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td>{{ $app->vehicle_no }}</td>
                            <td>{{ $app->vehicle_type }}</td>
                            <td>
                                @if($app->license_card_path)
                                    <a class="doc-link" href="{{ asset('storage/' . $app->license_card_path) }}" target="_blank">{{ __('Kad Lesen') }}</a><br>
                                @endif
                                @if($app->parent_permission_path)
                                    <a class="doc-link" href="{{ asset('storage/' . $app->parent_permission_path) }}" target="_blank">{{ __('Surat Ibu Bapa') }}</a><br>
                                @endif
                                @if($app->vehicle_photo_path)
                                    <a class="doc-link" href="{{ asset('storage/' . $app->vehicle_photo_path) }}" target="_blank">{{ __('Gambar Kenderaan') }}</a>
                                @endif
                            </td>
                            <td><span class="status-badge status-{{ strtolower($app->status) }}">{{ __($app->status) }}</span></td>
                            <td>{{ $app->approved_by_name ?: '-' }}</td>
                            <td>{{ $app->created_at ? \Illuminate\Support\Carbon::parse($app->created_at)->format('Y-m-d') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:#7a6555;">{{ __('Tiada rekod permohonan sticker.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px;">{{ $applications->links() }}</div>
</div>
@endsection

