@extends('layouts.app')

@section('title', 'Tambah Pelajar')



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Tambah Pelajar') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.students.store') }}">
        @csrf
        <div class="card">
            <h2>{{ __('Maklumat Pelajar') }}</h2>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="full_name">{{ __('Nama Penuh') }}</label>
                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required>
                    </div>
                    <div>
                        <label for="program">{{ __('Program') }}</label>
                        <input id="program" type="text" name="program" value="{{ old('program') }}" required>
                    </div>
                </div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="matric_no">{{ __('No Matrik') }}</label>
                        <input id="matric_no" type="text" name="matric_no" value="{{ old('matric_no') }}">
                    </div>
                    <div>
                        <label for="ic_no">{{ __('No IC') }}</label>
                        <input id="ic_no" type="text" name="ic_no" value="{{ old('ic_no') }}" required>
                    </div>
                </div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="email">{{ __('Email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label for="phone">{{ __('Telefon') }}</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}">
                    </div>
                    <div>
                        <label for="address">{{ __('Alamat') }}</label>
                        <textarea id="address" name="address" rows="3">{{ old('address') }}</textarea>
                    </div>
                </div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="residence_status">{{ __('Status Kediaman') }}</label>
                        <select id="residence_status" name="residence_status" required>
                            <option value="inside_campus" @selected(old('residence_status', 'inside_campus') === 'inside_campus')>{{ __('Dalam Kampus') }}</option>
                            <option value="live_out" @selected(old('residence_status') === 'live_out')>{{ __('Live Out / Luar Kampus') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="room_number">{{ __('No. Bilik / Kolej Kediaman') }}</label>
                        <input id="room_number" type="text" name="room_number" value="{{ old('room_number') }}" placeholder="{{ __('Contoh: AL306') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Pelajar') }}</button>
            <a class="btn" href="{{ route('admin.students.index') }}">{{ __('Batal') }}</a>
        </div>
    </form>
</div>
@endsection


