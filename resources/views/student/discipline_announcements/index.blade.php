@extends('layouts.app')

@section('title', __('Discipline Announcements'))

@push('styles')
<style>
    .disc-page {
        width: min(1120px, 100%);
        margin: 0 auto;
        display: grid;
        gap: 18px;
    }
    .disc-hero,
    .disc-card {
        border: 1px solid rgba(226, 209, 192, .14);
        border-radius: 24px;
        background:
            radial-gradient(circle at top right, rgba(215, 191, 168, .08), transparent 34%),
            linear-gradient(180deg, rgba(29, 26, 23, .96), rgba(17, 15, 13, .98));
        box-shadow: 0 22px 48px rgba(0, 0, 0, .24), inset 0 1px 0 rgba(255,255,255,.04);
    }
    .disc-hero {
        padding: 26px 28px;
        display: grid;
        gap: 12px;
    }
    .disc-eyebrow {
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
    .disc-hero h3 {
        margin: 0;
        font-size: clamp(1.7rem, 2.8vw, 2.4rem);
        line-height: 1.08;
        letter-spacing: -.04em;
        color: #f7efe8;
    }
    .disc-hero p {
        margin: 0;
        max-width: 760px;
        color: #c8b8a9;
        font-size: 1rem;
        line-height: 1.75;
    }
    .disc-toolbar {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }
    .disc-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .disc-chip {
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
    .disc-chip:hover {
        transform: translateY(-1px);
        border-color: rgba(226, 209, 192, .28);
        background: rgba(255,255,255,.08);
    }
    .disc-chip.primary {
        background: linear-gradient(135deg, #c9ae95 0%, #ecd7c3 100%);
        border-color: rgba(215, 191, 168, .55);
        color: #2a1d15;
        box-shadow: 0 12px 28px rgba(201, 174, 149, .22);
    }
    .disc-card {
        overflow: hidden;
    }
    .disc-head {
        padding: 18px 22px;
        border-bottom: 1px solid rgba(226, 209, 192, .12);
        display: grid;
        gap: 4px;
    }
    .disc-head strong {
        color: #f7efe8;
        font-size: 1.15rem;
    }
    .disc-head span {
        color: #a99888;
        font-size: .88rem;
    }
    .disc-list {
        display: grid;
    }
    .disc-item {
        padding: 20px 22px;
        border-top: 1px solid rgba(226, 209, 192, .1);
        display: grid;
        gap: 12px;
    }
    .disc-item:first-child {
        border-top: 0;
    }
    .disc-item-top {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        flex-wrap: wrap;
    }
    .disc-title {
        margin: 0;
        color: #f7efe8;
        font-size: 1.18rem;
        font-weight: 800;
        letter-spacing: -.02em;
    }
    .disc-date {
        color: #a99888;
        font-size: .84rem;
        white-space: nowrap;
    }
    .disc-meta {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .disc-pill {
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
    .disc-author {
        color: #b8a898;
        font-size: .88rem;
    }
    .disc-body {
        margin: 0;
        color: #ddd0c4;
        font-size: .95rem;
        line-height: 1.8;
        white-space: pre-line;
    }
    .disc-empty {
        padding: 36px 22px;
        color: #b8a898;
        text-align: center;
        font-size: .95rem;
    }
    .disc-pagination {
        display: flex;
        justify-content: center;
    }
    .disc-pagination nav {
        width: 100%;
    }
    @media (max-width: 960px) {
        .disc-page { gap: 16px; }
    }
    @media (max-width: 640px) {
        .disc-page {
            gap: 14px;
        }
        .disc-hero,
        .disc-head,
        .disc-item {
            padding-left: 16px;
            padding-right: 16px;
        }
        .disc-hero {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .disc-title {
            font-size: 1.02rem;
        }
        .disc-body {
            font-size: .92rem;
        }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;">{{ __('Discipline Announcements') }}</h2>
@endsection

@section('content')
<div class="disc-page">
    <section class="disc-hero">
        <span class="disc-eyebrow">{{ __('Student Discipline Portal') }}</span>
        <h3>{{ __('Latest discipline notices for students.') }}</h3>
        <p>{{ __('Check new updates from the discipline team here.') }}</p>
    </section>

    <div class="disc-toolbar">
        <div class="disc-actions">
            <a class="disc-chip" href="{{ route('student.dashboard') }}">{{ __('Back to Dashboard') }}</a>
            <a class="disc-chip" href="{{ route('student.offenses.index') }}">{{ __('My Offenses') }}</a>
            <a class="disc-chip primary" href="{{ route('student.rules.index') }}">{{ __('View Rules') }}</a>
        </div>
    </div>

    <section class="disc-card">
        <div class="disc-head">
            <strong>{{ __('Announcement Board') }}</strong>
            <span>{{ __('Latest posted announcements.') }}</span>
        </div>

        @if($announcements->count())
            <div class="disc-list">
                @foreach($announcements as $item)
                    <article class="disc-item">
                        <div class="disc-item-top">
                            <h3 class="disc-title">{{ $item->title }}</h3>
                            <div class="disc-date">
                                {{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('d M Y') : '-' }}
                            </div>
                        </div>

                        <div class="disc-meta">
                            <span class="disc-pill">{{ __('Discipline Notice') }}</span>
                            <span class="disc-author">{{ __('Published by') }} {{ $item->admin_name }}</span>
                        </div>

                        <p class="disc-body">{{ $item->body }}</p>
                    </article>
                @endforeach
            </div>
        @else
            <div class="disc-empty">{{ __('No discipline announcements are available right now.') }}</div>
        @endif
    </section>

    <div class="disc-pagination">{{ $announcements->links() }}</div>
</div>
@endsection
