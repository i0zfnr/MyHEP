@extends('layouts.app')
@section('title', __('Program Merit Points'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Merit Points') }}</h2>@endsection

@push('styles')
@php($canUseAccent = session('auth_user.admin_role') === 'system_admin')
@include('admin.programs.partials.design-system')

@endpush

@section('content')
<main class="point-page">
    <section class="point-card">
        <header class="point-head">
            <h1>{{ __('Student Program Merit Points Leaderboard') }}</h1>
            <p>{{ __('Ranks Politeknik Besut students using valid internal program attendance and merit points configured by each Program Director.') }}</p>
        </header>

        <form class="point-filter" method="get">
            <input name="q" value="{{ $search }}" placeholder="{{ __('Search student, matric number, or programme') }}">
            <button class="pmr-btn primary" type="submit">{{ __('Search') }}</button>
        </form>

        @if($students->isEmpty())
            <div class="point-empty">{{ __('No valid internal program attendance has earned merit points yet.') }}</div>
        @else
            <div class="point-table-wrap">
                <table class="point-table">
                    <thead><tr><th>{{ __('Rank') }}</th><th>{{ __('Student') }}</th><th>{{ __('Matric No.') }}</th><th>{{ __('Programme') }}</th><th>{{ __('Programs Joined') }}</th><th>{{ __('Total Merit') }}</th></tr></thead>
                    <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td><span class="point-rank">{{ $students->firstItem() + $loop->index }}</span></td>
                            <td><strong>{{ $student->full_name }}</strong></td>
                            <td>{{ $student->matric_no }}</td>
                            <td>{{ $student->program ?: '-' }}</td>
                            <td>{{ number_format($student->programs_joined) }}</td>
                            <td><span class="point-total">{{ number_format($student->total_points) }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:1rem 1.5rem;">{{ $students->links() }}</div>
        @endif
    </section>
</main>
@endsection
