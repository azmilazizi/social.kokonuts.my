@php
    use Carbon\Carbon;
    use Modules\AppChannels\Models\Accounts;
    use Modules\AppPublishing\Models\Posts;

    $teamId = request()->team_id;
    $startDate = Carbon::now()->subDays(30);
    $endDate = Carbon::now();
    $networks = [
        [
            'key' => 'facebook',
            'label' => __('Facebook'),
            'icon' => 'fa-brands fa-facebook-f',
            'color' => '#1877f2',
        ],
        [
            'key' => 'instagram',
            'label' => __('Instagram'),
            'icon' => 'fa-brands fa-instagram',
            'color' => '#e1306c',
        ],
    ];

    $accountsByNetwork = Accounts::query()
        ->where('team_id', $teamId)
        ->where('status', '!=', 0)
        ->whereIn('social_network', collect($networks)->pluck('key'))
        ->get()
        ->groupBy('social_network');
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header">
        <div class="fw-6">{{ __('Facebook & Instagram Analytics') }}</div>
        <div class="text-gray-500 fs-12">{{ __('Last 30 days') }}</div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            @foreach($networks as $network)
                @php
                    $accountCount = ($accountsByNetwork[$network['key']] ?? collect())->count();
                    $totalPosts = Posts::query()
                        ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
                        ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                        ->where('social_network', $network['key'])
                        ->count();
                    $successPosts = Posts::query()
                        ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
                        ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                        ->where('social_network', $network['key'])
                        ->where('status', 5)
                        ->count();
                    $successRate = $totalPosts > 0 ? round($successPosts * 100 / $totalPosts, 1) : 0;
                @endphp

                <div class="col-md-6">
                    <div class="card border-gray-300 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-12 mb-3">
                                <div class="d-flex align-items-center justify-content-center size-44 b-r-12 text-white" style="background-color: {{ $network['color'] }};">
                                    <i class="{{ $network['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="fw-6">{{ __(':network Analytics', ['network' => $network['label']]) }}</div>
                                    <div class="text-gray-500 fs-12">{{ __('Last 30 days') }}</div>
                                </div>
                            </div>

                            @if($accountCount === 0)
                                <div class="text-gray-600 fs-13">
                                    {{ __('Connect your social accounts to start tracking analytics and gain insights into your performance.') }}
                                </div>
                            @else
                                <div class="row g-3">
                                    <div class="col-4">
                                        <div class="text-gray-500 fs-12">{{ __('Connected Accounts') }}</div>
                                        <div class="fw-6 fs-18">{{ number_format($accountCount) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-gray-500 fs-12">{{ __('Posts') }}</div>
                                        <div class="fw-6 fs-18">{{ number_format($totalPosts) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-gray-500 fs-12">{{ __('Success Rate') }}</div>
                                        <div class="fw-6 fs-18">{{ $successRate }}%</div>
                                    </div>
                                </div>

                                @if($totalPosts === 0)
                                    <div class="text-gray-500 fs-13 mt-3">
                                        {{ __('No analytics data available.') }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
