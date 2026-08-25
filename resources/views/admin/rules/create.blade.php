@extends('layouts.app')

@section('title', 'Tambah Peraturan')



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Tambah Peraturan') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.rules.store') }}">
        @csrf
        <div class="card">
            <h2>{{ __('Maklumat Peraturan') }}</h2>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="title">{{ __('Tajuk') }}</label>
                        <input id="title" type="text" name="title" value="{{ old('title') }}" required>
                    </div>
                    <div>
                        <label for="category_id">{{ __('Kategori') }}</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">{{ __('Pilih kategori') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string)old('category_id') === (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <label for="description">{{ __('Penerangan') }}</label>
                    <textarea id="description" name="description" rows="7" required>{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Peraturan') }}</button>
            <a class="btn" href="{{ route('admin.rules.index') }}">{{ __('Batal') }}</a>
        </div>
    </form>
</div>
@endsection


