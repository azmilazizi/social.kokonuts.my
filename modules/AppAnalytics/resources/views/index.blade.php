@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Social Analytics') }}"
        description="{{ __('Track and compare performance across social media platforms.') }}"
    >
    </x-sub-header>
@endsection

@section('content')
    <div class="container pb-4">
        @if($channelsByNetwork->isEmpty())
            <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
                <span class="fs-70 mb-3 text-primary">
                    <i class="fa-light fa-chart-line"></i>
                </span>
                <div class="fw-semibold fs-5 mb-2 text-gray-800">
                    {{ __('No analytics data available.') }}
                </div>
                <div class="text-body-secondary mb-4 text-center">
                    {{ __('Connect your social accounts to start tracking analytics and gain insights into your performance.') }}
                </div>
                <a class="btn btn-dark" href="{{ url('app/channels') }}">
                    <i class="fa-light fa-plus me-1"></i> {{ __('Add Channel') }}
                </a>
            </div>
        @else
            @foreach($channelsByNetwork as $group)
                <div class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="fw-semibold mb-0">{{ __($group['label']) }}</h4>
                    </div>
                    <div class="row g-4">
                        @foreach($group['accounts'] as $account)
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
                                <div class="card shadow-none border-gray-300 h-100 analytics-card analytics-channel-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center gap-8">
                                            <div class="position-relative analytics-channel-avatar d-flex align-items-center justify-content-center">
                                                <a href="{{ $account->url ?? '#' }}" target="_blank" class="text-gray-900 text-hover-primary">
                                                    <img
                                                        data-src="{{ Media::url($account->avatar) }}"
                                                        src="{{ theme_public_asset('img/default.png') }}"
                                                        class="w-full h-full lazyload"
                                                        onerror="this.src='{{ theme_public_asset('img/default.png') }}'"
                                                    >
                                                </a>
                                                @if($account->module_item)
                                                    <span class="analytics-channel-badge position-absolute d-flex align-items-center justify-content-center text-center text-white border-1 b-0 r-0" style="background-color: {{ $account->module_item['color'] ?? '#6f58ff' }};">
                                                        <i class="{{ $account->module_item['icon'] ?? 'fa-light fa-chart-line' }}"></i>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="analytics-channel-title text-truncate">
                                                    <a href="{{ $account->url ?? '#' }}" target="_blank" class="text-gray-900 text-hover-primary">
                                                        {{ $account->name }}
                                                    </a>
                                                </div>
                                                <div class="analytics-channel-subtitle text-truncate">
                                                    {{ __(ucfirst($account->social_network.' '.$account->category)) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer fs-12">
                                        <a href="{{ route('app.analytics.show', ['platform' => strtolower($account->social_network), 'id' => $account->id_secure]) }}" class="analytics-channel-link d-flex w-100 gap-8 align-items-center justify-content-center text-hover-primary">
                                            <i class="fa-light fa-chart-simple"></i>
                                            <span>{{ __('View') }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
