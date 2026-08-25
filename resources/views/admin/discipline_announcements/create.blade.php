@extends('layouts.app')

@section('title', __('Tambah Pengumuman Disiplin'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Tambah Pengumuman Disiplin') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.discipline-announcements.store') }}">
        @csrf
        <div class="card">
            <h2>{{ __('Maklumat Pengumuman') }}</h2>
            <div class="body">
                <div>
                    <label for="title">{{ __('Tajuk') }}</label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" required>
                </div>

                <div style="margin-top:12px;">
                    <label for="body">{{ __('Penerangan') }}</label>
                    <textarea id="body" name="body" rows="7" required>{{ old('body') }}</textarea>
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Pengumuman') }}</button>
            <a class="btn" href="{{ route('admin.discipline-announcements.index') }}">{{ __('Batal') }}</a>
        </div>
    </form>
</div>
@endsection


