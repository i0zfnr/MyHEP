@extends('layouts.app')

@section('title', $title)

@section('header')
    <div class="module-dashboard-page-heading">
        <span>{{ __('Analytics') }} <i aria-hidden="true">/</i> {{ $eyebrow }}</span>
        <h2>{{ $title }}</h2>
    </div>
@endsection

@section('content')
<div class="wrap module-dashboard-page">
    <section class="module-dashboard-hero">
        <div>
            <span class="module-dashboard-eyebrow">{{ $eyebrow }} · {{ __('Overview') }}</span>
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>
        </div>
        <div class="module-dashboard-actions">
            <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Main Dashboard') }}</a>
            <a class="btn btn-primary" href="{{ $recordsUrl }}">{{ $recordsLabel }}</a>
        </div>
    </section>

    <section class="module-dashboard-kpis" aria-label="{{ __('Key figures') }}">
        @foreach($cards as $card)
            <article class="module-dashboard-kpi tone-{{ $card['tone'] }}">
                <span>{{ $card['label'] }}</span>
                <strong>{{ $card['value'] }}</strong>
                <small>{{ $card['note'] }}</small>
            </article>
        @endforeach
    </section>

    <section class="module-dashboard-charts" aria-label="{{ $eyebrow }} {{ __('visualizations') }}">
        @foreach($charts as $chart)
            @php
                $items = $chart['items'];
                $maxValue = max(1, ...array_column($items, 'value'));
            @endphp
            <article class="module-dashboard-chart {{ $loop->first ? 'is-wide' : '' }}">
                <header>
                    <div>
                        <h2>{{ $chart['title'] }}</h2>
                        <p>{{ $chart['description'] }}</p>
                    </div>
                    @if($items !== [])
                        <span class="module-dashboard-chart-total">{{ number_format(array_sum(array_column($items, 'value'))) }}</span>
                    @endif
                </header>
                @if($items === [] || array_sum(array_column($items, 'value')) === 0)
                    <div class="module-dashboard-empty">{{ __('There is not enough data to show this chart yet.') }}</div>
                @elseif($loop->first)
                    <div class="module-dashboard-month-chart" role="img" aria-label="{{ $chart['title'] }}">
                        @foreach($items as $item)
                            <div class="module-dashboard-month-column" title="{{ $item['label'] }}: {{ number_format($item['value']) }}">
                                <strong>{{ number_format($item['value']) }}</strong>
                                <div class="module-dashboard-month-track"><span style="height: {{ $item['value'] > 0 ? max(4, (int) round(($item['value'] / $maxValue) * 100)) : 0 }}%"></span></div>
                                <small>{{ $item['label'] }}</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="module-dashboard-bar-list">
                        @foreach($items as $item)
                            @php($percent = (int) round(($item['value'] / $maxValue) * 100))
                            <div class="module-dashboard-bar-row">
                                <div class="module-dashboard-bar-meta"><span>{{ $item['label'] }}</span><strong>{{ number_format($item['value']) }}</strong></div>
                                <div class="module-dashboard-bar-track" role="img" aria-label="{{ $item['label'] }}: {{ number_format($item['value']) }}">
                                    <span style="width: {{ $percent }}%"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        @endforeach
    </section>
</div>
@endsection
