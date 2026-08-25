@extends('layouts.app')

@section('title', __('Discipline Announcements'))



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
