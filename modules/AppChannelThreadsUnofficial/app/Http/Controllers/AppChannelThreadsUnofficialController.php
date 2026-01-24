<?php

namespace Modules\AppChannelThreadsUnofficial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JanuSoftware\Facebook\Facebook;

class AppChannelThreadsUnofficialController extends Controller
{
    public $fb;
    public $scopes;

    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));

        $appId = get_option('threads_app_id', '');
        $appSecret = get_option('threads_app_secret', '');
        $appVersion = get_option('threads_graph_version', 'v21.0');
        $appPermissions = get_option(
            'threads_permissions',
            'threads_basic,threads_content_publish,threads_manage_insights'
        );

        if (!$appId || !$appSecret || !$appVersion || !$appPermissions) {
            \Access::deny(__('To use Threads, you must first configure the app ID, app secret, permissions, and API version.'));
        }

        try {
            $this->fb = new Facebook([
                'app_id' => $appId,
                'app_secret' => $appSecret,
                'default_graph_version' => $appVersion,
            ]);
        } catch (\Exception $e) {
            \Access::deny(__('Could not connect to Threads API: ') . $e->getMessage());
        }

        $this->scopes = $appPermissions;
    }

    public function oauth(Request $request)
    {
        $request->session()->forget('Threads_AccessToken');
        $helper = $this->fb->getRedirectLoginHelper();
        $permissions = [$this->scopes];
        $callbackUrl = rtrim(module_url(), '/');
        $loginUrl = $helper->getLoginUrl($callbackUrl, $permissions);

        $parsedUrl = parse_url($loginUrl);
        $queryParams = [];

        if (!empty($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }

        $queryParams['client_id'] = $queryParams['client_id'] ?? get_option('threads_app_id', '');
        $queryParams['redirect_uri'] = $queryParams['redirect_uri'] ?? $callbackUrl;
        $queryParams['response_type'] = $queryParams['response_type'] ?? 'code';
        $queryParams['scope'] = $queryParams['scope'] ?? $this->scopes;

        $authorizeUrl = 'https://www.threads.com/oauth/authorize?' . http_build_query(array_merge(
            $queryParams,
            ['__coig_login' => '1']
        ));
        $loginUrl = 'https://www.threads.com/login?next=' . urlencode($authorizeUrl);

        return redirect($loginUrl);
    }

    public function proccess(Request $request)
    {
        return response()->json([
            'status' => 0,
            'message' => __('Manual credential entry is no longer supported. Please use OAuth login.'),
        ]);
    }

    public function index(Request $request)
    {
        $result = [];

        try {
            if (!session('Threads_AccessToken')) {
                if (!$request->code) {
                    return redirect(module_url('oauth'));
                }

                $callbackUrl = rtrim(module_url(), '/');
                $helper = $this->fb->getRedirectLoginHelper();
                if ($request->state) {
                    $helper->getPersistentDataHandler()->set('state', $request->state);
                }

                $accessToken = $helper->getAccessToken($callbackUrl);
                $accessToken = $accessToken->getValue();
                session(['Threads_AccessToken' => $accessToken]);

                return redirect($callbackUrl);
            }

            $accessToken = session('Threads_AccessToken');
            $profile = $this->fb->get('/me?fields=id,name,username,profile_picture_url', $accessToken)->getDecodedBody();

            if (!empty($profile['id'])) {
                $username = $profile['username'] ?? $profile['name'] ?? 'threads';

                $result[] = [
                    'id' => $profile['id'],
                    'name' => $profile['name'] ?? $username,
                    'username' => $username,
                    'avatar' => $profile['profile_picture_url'] ?? text2img($username),
                    'desc' => $profile['name'] ?? $username,
                    'link' => 'https://www.threads.net/@' . $username,
                    'oauth' => $accessToken,
                    'module' => $request->module['module_name'],
                    'reconnect_url' => $request->module['uri'] . '/oauth',
                    'social_network' => 'threads',
                    'category' => 'profile',
                    'login_type' => 1,
                    'can_post' => 1,
                    'data' => '',
                    'proxy' => 0,
                ];

                $channels = [
                    'status' => 1,
                    'message' => __('Succeeded'),
                ];
            } else {
                $channels = [
                    'status' => 0,
                    'message' => __('No profile to add'),
                ];
            }
        } catch (\Exception $e) {
            $channels = [
                'status' => 0,
                'message' => $e->getMessage(),
            ];
        }

        $channels = array_merge($channels, [
            'channels' => $result,
            'module' => $request->module,
            'save_url' => url_app('channels/save'),
            'reconnect_url' => module_url('oauth'),
            'oauth' => session('Threads_AccessToken'),
        ]);

        session(['channels' => $channels]);

        return redirect(url_app('channels/add'));
    }

    public function settings()
    {
        return view('appchannelthreadsunoofficial::settings');
    }
}
