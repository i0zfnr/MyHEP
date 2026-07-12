@extends('layouts.app')

@section('title', __('Discipline Rules'))

@push('styles')
<style>
    .rules-page {
        width: min(1120px, 100%);
        margin: 0 auto;
        display: grid;
        gap: 18px;
    }
    .rules-hero,
    .rules-panel {
        border: 1px solid rgba(226, 209, 192, .14);
        border-radius: 24px;
        background:
            radial-gradient(circle at top right, rgba(215, 191, 168, .08), transparent 34%),
            linear-gradient(180deg, rgba(29, 26, 23, .96), rgba(17, 15, 13, .98));
        box-shadow: 0 22px 48px rgba(0, 0, 0, .24), inset 0 1px 0 rgba(255,255,255,.04);
    }
    .rules-hero {
        padding: 26px 28px;
        display: grid;
        gap: 12px;
    }
    .rules-eyebrow {
        display: inline-flex;
        width: fit-content;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(95, 190, 145, .12);
        color: #d8f7e7;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .rules-hero h3 {
        margin: 0;
        font-size: clamp(1.7rem, 2.8vw, 2.4rem);
        line-height: 1.08;
        letter-spacing: -.04em;
        color: #f7efe8;
    }
    .rules-hero p {
        margin: 0;
        max-width: 780px;
        color: #c8b8a9;
        font-size: 1rem;
        line-height: 1.75;
    }
    .rules-toolbar {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }
    .rules-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .rules-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid rgba(226, 209, 192, .16);
        background: rgba(255,255,255,.03);
        color: #f7efe8;
        text-decoration: none;
        font-size: .92rem;
        font-weight: 700;
        transition: transform .18s ease, border-color .18s ease, background-color .18s ease;
    }
    .rules-chip:hover {
        transform: translateY(-1px);
        border-color: rgba(226, 209, 192, .28);
        background: rgba(255,255,255,.08);
    }
    .rules-chip.primary {
        background: linear-gradient(135deg, #c9ae95 0%, #ecd7c3 100%);
        border-color: rgba(215, 191, 168, .55);
        color: #2a1d15;
        box-shadow: 0 12px 28px rgba(201, 174, 149, .22);
    }
    .rules-panel {
        overflow: hidden;
    }
    .rules-panel-head {
        padding: 18px 22px;
        border-bottom: 1px solid rgba(226, 209, 192, .12);
        display: grid;
        gap: 4px;
    }
    .rules-panel-head strong {
        color: #f7efe8;
        font-size: 1.15rem;
    }
    .rules-panel-head span {
        color: #a99888;
        font-size: .88rem;
    }
    .rules-filters {
        padding: 16px 18px;
        border-bottom: 1px solid rgba(226, 209, 192, .1);
    }
    .rules-filter-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(220px, .8fr) auto;
        gap: 10px;
    }
    .rules-input,
    .rules-select {
        width: 100%;
        min-height: 48px;
        padding: 0 15px;
        border-radius: 14px;
        border: 1px solid rgba(226, 209, 192, .16);
        background: rgba(9, 8, 8, .55);
        color: #f7efe8;
        font: inherit;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
    }
    .rules-input::placeholder {
        color: #9d8b7a;
    }
    .rules-input:focus,
    .rules-select:focus {
        border-color: rgba(215, 191, 168, .45);
        box-shadow: 0 0 0 4px rgba(215, 191, 168, .12);
        background: rgba(9, 8, 8, .72);
    }
    .rules-filter-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .rules-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        padding: 0 16px;
        border: 1px solid rgba(226, 209, 192, .18);
        border-radius: 14px;
        background: rgba(255,255,255,.04);
        color: #f7efe8;
        text-decoration: none;
        font-size: .9rem;
        font-weight: 800;
        cursor: pointer;
        transition: transform .18s ease, border-color .18s ease, background-color .18s ease;
    }
    .rules-btn:hover {
        transform: translateY(-1px);
        border-color: rgba(226, 209, 192, .28);
        background: rgba(255,255,255,.08);
    }
    .rules-btn.primary {
        background: linear-gradient(135deg, #c9ae95 0%, #ecd7c3 100%);
        border-color: rgba(215, 191, 168, .55);
        color: #2a1d15;
        box-shadow: 0 12px 28px rgba(201, 174, 149, .22);
    }
    .rules-list {
        display: grid;
    }
    .rules-item {
        padding: 20px 22px;
        border-top: 1px solid rgba(226, 209, 192, .1);
        display: grid;
        gap: 12px;
    }
    .rules-item:first-child {
        border-top: 0;
    }
    .rules-meta {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    .rules-pill {
        display: inline-flex;
        align-items: center;
        padding: 7px 11px;
        border-radius: 999px;
        background: rgba(242, 201, 153, .12);
        color: #f2d3b4;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .rules-updated {
        color: #a99888;
        font-size: .84rem;
        white-space: nowrap;
    }
    .rules-title {
        margin: 0;
        color: #f7efe8;
        font-size: 1.14rem;
        font-weight: 800;
        letter-spacing: -.02em;
    }
    .rules-body {
        margin: 0;
        color: #ddd0c4;
        font-size: .95rem;
        line-height: 1.8;
        white-space: pre-line;
    }
    .rules-empty {
        padding: 36px 22px;
        color: #b8a898;
        text-align: center;
        font-size: .95rem;
    }
    .rules-pagination {
        display: flex;
        justify-content: center;
    }
    .rules-pagination nav {
        width: 100%;
    }
    @media (max-width: 960px) {
        .rules-filter-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 640px) {
        .rules-page {
            gap: 14px;
        }
        .rules-hero,
        .rules-panel-head,
        .rules-filters,
        .rules-item {
            padding-left: 16px;
            padding-right: 16px;
        }
        .rules-hero {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .rules-title {
            font-size: 1.02rem;
        }
        .rules-body {
            font-size: .92rem;
        }
        .rules-filter-actions {
            width: 100%;
        }
        .rules-btn {
            flex: 1 1 0;
        }
    }
</style>
@endpush

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
