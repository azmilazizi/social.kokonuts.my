@extends('layouts.app')

@section('content')
    <div class="container pb-4">
        <div class="card border-0 shadow-none bg-white mb-4 analytics-card">
            <div class="card-body py-5 text-center">
                <div class="position-relative d-inline-flex align-items-center justify-content-center mb-3">
                    <img
                        data-src="{{ Media::url($account->avatar) }}"
                        src="{{ theme_public_asset('img/default.png') }}"
                        class="b-r-100 size-80 border-1 lazyload"
                        onerror="this.src='{{ theme_public_asset('img/default.png') }}'"
                    >
                    @if($moduleItem)
                        <span class="size-18 border-1 b-r-100 position-absolute fs-9 d-flex align-items-center justify-content-between text-center text-white b-0 r-0" style="background-color: {{ $moduleItem['color'] ?? '#6f58ff' }};">
                            <div class="w-100"><i class="{{ $moduleItem['icon'] ?? 'fa-light fa-chart-line' }}"></i></div>
                        </span>
                    @endif
                </div>
                <div class="fw-semibold fs-3 mb-1">{{ $account->name }}</div>
                @if($account->username)
                    <div class="text-gray-600 mb-1">{{ $account->username }}</div>
                @endif
                @if($account->url)
                    <a class="text-primary" href="{{ $account->url }}" target="_blank">{{ $account->url }}</a>
                @endif
                <div class="mt-3 text-gray-700">
                    <span class="fw-semibold">8,632</span> {{ __('Fans') }}
                    <span class="mx-2 text-gray-400">|</span>
                    <span class="fw-semibold">89,102</span> {{ __('Followers') }}
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-end gap-3 mb-4">
            <button class="btn btn-dark btn-sm" type="button">
                <i class="fa-light fa-file-arrow-down me-1"></i> {{ __('Export PDF') }}
            </button>
            <button class="btn btn-outline btn-light btn-sm" type="button">
                <i class="fa-light fa-calendar-days me-1"></i> December 27, 2025 - January 23, 2026
                <i class="fa-light fa-chevron-down ms-1"></i>
            </button>
        </div>

        <div class="row g-4 mb-4">
            @foreach($stats as $stat)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card shadow-none border-gray-300 h-100 analytics-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-12">
                                <span class="size-40 d-flex align-items-center justify-content-center b-r-100 bg-{{ $stat['tone'] }}-100 text-{{ $stat['tone'] }}">
                                    <i class="{{ $stat['icon'] }}"></i>
                                </span>
                                <div>
                                    <div class="text-gray-600 fw-semibold">{{ $stat['label'] }}</div>
                                    <div class="fs-3 fw-semibold text-gray-900">{{ $stat['value'] }}</div>
                                    <div class="text-success fs-12">
                                        <i class="fa-light fa-arrow-trend-up me-1"></i>{{ $stat['trend'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card shadow-none border-gray-300 h-100 analytics-card">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">{{ __('Overview Trends') }}</div>
                        <div class="bg-light b-r-10 d-flex align-items-center justify-content-center" style="min-height: 220px;">
                            <span class="text-gray-600">{{ __('Analytics data is not yet available for this section. Please check back later.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card shadow-none border-gray-300 h-100 analytics-card">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">{{ __('Fans History') }}</div>
                        <div class="bg-light b-r-10 d-flex align-items-center justify-content-center" style="min-height: 220px;">
                            <span class="text-gray-600">{{ __('Analytics data is not yet available for this section. Please check back later.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-8">
                <div class="card shadow-none border-gray-300 h-100 analytics-card">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">{{ __('Gained & Lost Fans') }}</div>
                        <div class="bg-light b-r-10 d-flex align-items-center justify-content-center" style="min-height: 240px;">
                            <span class="text-gray-600">{{ __('Analytics data is not yet available for this section. Please check back later.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card shadow-none border-gray-300 mb-4 analytics-card">
                    <div class="card-body">
                        <div class="text-gray-600">{{ __('Gained Fans') }}</div>
                        <div class="fs-3 fw-semibold">70,635</div>
                    </div>
                </div>
                <div class="card shadow-none border-gray-300 mb-4 analytics-card">
                    <div class="card-body">
                        <div class="text-gray-600">{{ __('Lost Fans') }}</div>
                        <div class="fs-3 fw-semibold">79,783</div>
                    </div>
                </div>
                <div class="card shadow-none border-gray-300 analytics-card">
                    <div class="card-body">
                        <div class="text-gray-600">{{ __('Net Fans') }}</div>
                        <div class="fs-3 fw-semibold">-9,148</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card shadow-none border-gray-300 analytics-card">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">{{ __('Post Reach') }}</div>
                        <div class="bg-light b-r-10 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                            <span class="text-gray-600">{{ __('Analytics data is not yet available for this section. Please check back later.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card shadow-none border-gray-300 analytics-card">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">{{ __('Post Impressions') }}</div>
                        <div class="bg-light b-r-10 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                            <span class="text-gray-600">{{ __('Analytics data is not yet available for this section. Please check back later.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card shadow-none border-gray-300 h-100 analytics-card">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">{{ __('Page Views') }}</div>
                        <div class="bg-light b-r-10 d-flex align-items-center justify-content-center" style="min-height: 220px;">
                            <span class="text-gray-600">{{ __('Analytics data is not yet available for this section. Please check back later.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card shadow-none border-gray-300 h-100 analytics-card">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">{{ __('Post Engagements') }}</div>
                        <div class="bg-light b-r-10 d-flex align-items-center justify-content-center" style="min-height: 220px;">
                            <span class="text-gray-600">{{ __('Analytics data is not yet available for this section. Please check back later.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <div class="card shadow-none border-gray-300 h-100 analytics-card">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">{{ __('Fans Location') }}</div>
                        <div class="bg-light b-r-10 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                            <span class="text-gray-600">{{ __('Analytics data is not yet available for this section. Please check back later.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="card shadow-none border-gray-300 h-100 analytics-card">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">{{ __('Top Countries') }}</div>
                        <div class="bg-light b-r-10 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                            <span class="text-gray-600">{{ __('Analytics data is not yet available for this section. Please check back later.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
