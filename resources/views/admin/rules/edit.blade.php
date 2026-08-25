@extends('layouts.app')

@section('title', 'Edit Peraturan')



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Edit Peraturan') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.rules.update', $rule->id) }}">
        @csrf
        @method('PUT')
        <div class="card">
            <h2>{{ __('Maklumat Peraturan') }}</h2>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="title">{{ __('Tajuk') }}</label>
                        <input id="title" type="text" name="title" value="{{ old('title', $rule->title) }}" required>
                    </div>
                    <div>
                        <label for="category_id">{{ __('Kategori') }}</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">{{ __('Pilih kategori') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string)old('category_id', $rule->category_id) === (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <label for="description">{{ __('Penerangan') }}</label>
                    <textarea id="description" name="description" rows="7" required>{{ old('description', $rule->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Perubahan') }}</button>
            <a class="btn" href="{{ route('admin.rules.index') }}">{{ __('Batal') }}</a>
        </div>
    </form>
</div>
@endsection


