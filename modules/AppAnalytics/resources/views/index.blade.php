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
                                    <div class="card-body d-flex align-items-center gap-16">
                                        <div class="text-gray-600 analytics-channel-avatar d-flex align-items-center justify-content-between position-relative">
                                            <img
                                                data-src="{{ Media::url($account->avatar) }}"
                                                src="{{ theme_public_asset('img/default.png') }}"
                                                class="b-r-100 w-full h-full border-1 lazyload"
                                                onerror="this.src='{{ theme_public_asset('img/default.png') }}'"
                                            >
                                            @if($account->module_item)
                                                <span class="size-16 border-1 b-r-100 position-absolute fs-9 d-flex align-items-center justify-content-between text-center text-white b-0 r-0 analytics-channel-badge" style="background-color: {{ $account->module_item['color'] ?? '#6f58ff' }};">
                                                    <div class="w-100"><i class="{{ $account->module_item['icon'] ?? 'fa-light fa-chart-line' }}"></i></div>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 fs-14 fw-5 text-truncate">
                                            <div class="text-truncate text-gray-900">{{ $account->name }}</div>
                                            <div class="fs-12 text-gray-600 text-truncate">
                                                {{ __(ucfirst($account->social_network.' '.$account->category)) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer fs-12 d-flex justify-content-center">
                                        <a class="btn btn-sm analytics-channel-link" href="{{ route('app.analytics.show', ['platform' => strtolower($account->social_network), 'id' => $account->id_secure]) }}">
                                            <i class="fa-light fa-chart-simple me-1"></i> {{ __('View') }}
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
