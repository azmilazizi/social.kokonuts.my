<?php

namespace Modules\AppAnalytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use Modules\AppChannels\Models\Accounts;

class AppAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Accounts::where('team_id', $request->team_id)
            ->where('status', '!=', 0)
            ->orderBy('social_network')
            ->orderBy('name')
            ->get();

        $moduleData = [];
        foreach ($accounts as $account) {
            if (!isset($moduleData[$account->social_network])) {
                if ($module = Module::find($account->module)) {
                    $moduleData[$account->social_network] = $module->get('menu');
                }
            }

            $account->module_item = $moduleData[$account->social_network] ?? null;
        }

        $channelsByNetwork = $accounts->groupBy('social_network')->map(function ($items, $network) {
            $moduleItem = $items->first()->module_item ?? null;

            return [
                'label' => $moduleItem['name'] ?? ucfirst($network),
                'accounts' => $items,
            ];
        });

        return view('appanalytics::index', [
            'channelsByNetwork' => $channelsByNetwork,
        ]);
    }

    public function show(Request $request, string $platform, string $id)
    {
        $account = Accounts::where('team_id', $request->team_id)
            ->where('id_secure', $id)
            ->where('status', '!=', 0)
            ->firstOrFail();

        if (strtolower($account->social_network) !== strtolower($platform)) {
            abort(404);
        }

        $moduleItem = null;
        if ($module = Module::find($account->module)) {
            $moduleItem = $module->get('menu');
        }

        $stats = [
            [
                'label' => __('Likes'),
                'value' => '70,635',
                'trend' => '+100% '.__('vs last period'),
                'icon' => 'fa-light fa-thumbs-up',
                'tone' => 'primary',
            ],
            [
                'label' => __('Reach'),
                'value' => '71,708',
                'trend' => '+100% '.__('vs last period'),
                'icon' => 'fa-light fa-eye',
                'tone' => 'success',
            ],
            [
                'label' => __('Impressions'),
                'value' => '74,442',
                'trend' => '+100% '.__('vs last period'),
                'icon' => 'fa-light fa-rotate',
                'tone' => 'warning',
            ],
            [
                'label' => __('Engagements'),
                'value' => '64,736',
                'trend' => '+100% '.__('vs last period'),
                'icon' => 'fa-light fa-comment-dots',
                'tone' => 'danger',
            ],
            [
                'label' => __('Page Views'),
                'value' => '73,536',
                'trend' => '+100% '.__('vs last period'),
                'icon' => 'fa-light fa-binoculars',
                'tone' => 'secondary',
            ],
            [
                'label' => __('Published Posts'),
                'value' => '48',
                'trend' => '+100% '.__('vs last period'),
                'icon' => 'fa-light fa-paper-plane',
                'tone' => 'info',
            ],
        ];

        return view('appanalytics::show', [
            'account' => $account,
            'moduleItem' => $moduleItem,
            'stats' => $stats,
        ]);
    }
}
