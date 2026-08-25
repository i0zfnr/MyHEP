@extends('layouts.app')

@section('title', __('Discipline Rules'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;">{{ __('Discipline Rules') }}</h2>
@endsection

@section('content')
<div class="rules-page">
    <section class="rules-hero">
        <span class="rules-eyebrow">{{ __('Student Conduct Guide') }}</span>
        <h3>{{ __('Campus discipline rules for students.') }}</h3>
        <p>{{ __('Review the rules and search by keyword or category.') }}</p>
    </section>

    <div class="rules-toolbar">
        <div class="rules-actions">
            <a class="rules-chip" href="{{ route('student.dashboard') }}">{{ __('Back to Dashboard') }}</a>
            <a class="rules-chip" href="{{ route('student.offenses.index') }}">{{ __('Check Offense') }}</a>
            <a class="rules-chip" href="{{ route('student.vehicle-stickers.index') }}">{{ __('Sticker Application') }}</a>
            <a class="rules-chip primary" href="{{ route('student.scholarships.index') }}">{{ __('Scholarship Portal') }}</a>
        </div>
    </div>

    <section class="rules-panel">
        <div class="rules-panel-head">
            <strong>{{ __('Rule Directory') }}</strong>
            <span>{{ __('Search or filter the rule list.') }}</span>
        </div>

        <div class="rules-filters">
            <form method="GET" action="{{ route('student.rules.index') }}">
                <div class="rules-filter-grid">
                    <input
                        class="rules-input"
                        type="text"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="{{ __('Search title / description') }}"
                    >
                    <select class="rules-select" name="category_id">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string)($filters['category_id'] ?? '') === (string)$category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="rules-filter-actions">
                        <button class="rules-btn primary" type="submit">{{ __('Filter') }}</button>
                        <a class="rules-btn" href="{{ route('student.rules.index') }}">{{ __('Reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        @if($rules->count())
            <div class="rules-list">
                @foreach($rules as $rule)
                    <article class="rules-item">
                        <div class="rules-meta">
                            <span class="rules-pill">{{ $rule->category_name }}</span>
                            <span class="rules-updated">
                                {{ __('Updated') }}:
                                {{ $rule->updated_at ? \Illuminate\Support\Carbon::parse($rule->updated_at)->format('d M Y · H:i') : '-' }}
                            </span>
                        </div>

                        <h3 class="rules-title">{{ $rule->title }}</h3>
                        <p class="rules-body">{{ $rule->description }}</p>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rules-empty">{{ __('No discipline rules are available for the current filter.') }}</div>
        @endif
    </section>

    <div class="rules-pagination">{{ $rules->links() }}</div>
</div>
@endsection
