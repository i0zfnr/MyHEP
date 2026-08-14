@extends('layouts.app')
@section('title', __('Program Participation Points'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Participation Points') }}</h2>@endsection

@push('styles')
@php($canUseAccent = session('auth_user.admin_role') === 'system_admin')
@include('admin.programs.partials.design-system')
<style>
.point-page{max-width:1540px;margin:0 auto;padding:1.5rem;display:grid;gap:1rem}.point-card{overflow:hidden;border:1px solid var(--border,#e6d8c7);border-radius:18px;background:var(--surface,#fff);box-shadow:var(--glass-shadow,0 14px 36px rgba(0,0,0,.06))}.point-head{padding:1.35rem 1.5rem;border-bottom:1px solid var(--border,#e6d8c7)}.point-head h1{margin:0 0 .35rem;font-size:1.65rem}.point-head p{margin:0;color:var(--text-muted,#746b62)}.point-filter{display:flex;gap:.65rem;padding:1rem 1.5rem}.point-filter input{min-height:44px;flex:1;padding:.7rem .9rem;border:1px solid var(--border,#e6d8c7);border-radius:11px;background:var(--surface,#fff);color:var(--text,#241d16)}.point-table-wrap{overflow:auto}.point-table{width:100%;border-collapse:collapse}.point-table th,.point-table td{padding:.9rem 1rem;border-top:1px solid var(--border,#e6d8c7);text-align:left}.point-table th{font-size:.72rem;text-transform:uppercase;color:var(--text-muted,#746b62)}.point-rank{width:42px;height:42px;display:grid;place-items:center;border-radius:50%;background:color-mix(in srgb,var(--se-primary,#99702d) 14%,var(--surface,#fff));font-weight:900;color:var(--se-primary,#99702d)}.point-total{font-size:1.1rem;font-weight:900;color:var(--se-primary,#99702d)}.point-empty{padding:3rem 1.5rem;text-align:center;color:var(--text-muted,#746b62)}
@media(max-width:700px){.point-page{padding:1rem}.point-filter{flex-direction:column}.point-table th:nth-child(4),.point-table td:nth-child(4){display:none}}
</style>
@endpush

@section('content')
<main class="point-page">
    <section class="point-card">
        <header class="point-head">
            <h1>{{ __('Student Program Participation Leaderboard') }}</h1>
            <p>{{ __('Ranks Politeknik Besut students using valid internal program attendance and points configured by each Program Director.') }}</p>
        </header>

        <form class="point-filter" method="get">
            <input name="q" value="{{ $search }}" placeholder="{{ __('Search student, matric number, or programme') }}">
            <button class="pmr-btn primary" type="submit">{{ __('Search') }}</button>
        </form>

        @if($students->isEmpty())
            <div class="point-empty">{{ __('No valid internal program attendance has earned participation points yet.') }}</div>
        @else
            <div class="point-table-wrap">
                <table class="point-table">
                    <thead><tr><th>{{ __('Rank') }}</th><th>{{ __('Student') }}</th><th>{{ __('Matric No.') }}</th><th>{{ __('Programme') }}</th><th>{{ __('Programs Joined') }}</th><th>{{ __('Total Points') }}</th></tr></thead>
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
