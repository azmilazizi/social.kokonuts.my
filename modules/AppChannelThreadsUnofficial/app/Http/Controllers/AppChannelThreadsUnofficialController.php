<?php

namespace Modules\AppChannelThreadsUnofficial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            $this->oauthClient = new Facebook([
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
        $clientId = get_option('threads_app_id');
        $redirectUri = module_url(); // https://social.kokonuts.my/app/threads/profile
        $scope = get_option('threads_permissions', 'threads_basic');
        $state = bin2hex(random_bytes(16));

        $request->session()->put('threads_oauth_state', $state);

        $authorizeUrl = 'https://www.threads.com/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

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
            // ====== CONFIG ======
            $clientId     = get_option('threads_app_id', '');
            $clientSecret = get_option('threads_app_secret', '');
            $scopes       = get_option('threads_permissions', 'threads_basic');
            $redirectUri  = module_url(); // MUST match exactly what you whitelisted in Meta Threads settings

            if (!$clientId || !$clientSecret) {
                throw new \Exception('Threads app ID/secret is missing. Please configure Threads credentials.');
            }

            // ====== STEP 1: If we don't have a token yet, handle OAuth callback or start OAuth ======
            if (!session('Threads_AccessToken')) {

                // No code? Start OAuth
                if (!$request->has('code')) {
                    // create & store state
                    $state = bin2hex(random_bytes(16));
                    $request->session()->put('threads_oauth_state', $state);

                    // build authorize url (Threads)
                    $authorizeUrl = 'https://www.threads.com/oauth/authorize?' . http_build_query([
                        'client_id'     => $clientId,
                        'redirect_uri'  => $redirectUri,
                        'response_type' => 'code',
                        'scope'         => $scopes,     // comma-separated string is OK
                        'state'         => $state,
                    ], '', '&', PHP_QUERY_RFC3986);

                    // wrap with threads login
                    $loginUrl = 'https://www.threads.com/login?next=' . urlencode($authorizeUrl);

                    return redirect($loginUrl);
                }

                // Validate state (anti-CSRF)
                $expectedState = $request->session()->pull('threads_oauth_state');
                $incomingState = $request->get('state');

                if (!$expectedState || !$incomingState || !hash_equals($expectedState, $incomingState)) {
                    throw new \Exception('Invalid OAuth state. Please try connecting again.');
                }

                // Exchange code -> short-lived access token (Threads)
                // NOTE: This must be server-to-server. No Meta/Facebook app secret is needed here.
                $tokenResp = \Illuminate\Support\Facades\Http::asForm()->post('https://graph.threads.net/oauth/access_token', [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type'    => 'authorization_code',
                    'redirect_uri'  => $redirectUri,
                    'code'          => $request->get('code'),
                ]);

                if (!$tokenResp->ok()) {
                    throw new \Exception('Threads token exchange failed: ' . $tokenResp->body());
                }

                $tokenJson = $tokenResp->json();
                if (empty($tokenJson['access_token'])) {
                    throw new \Exception('Threads token exchange did not return an access_token: ' . json_encode($tokenJson));
                }

                session(['Threads_AccessToken' => $tokenJson['access_token']]);

                // Optional: exchange short-lived -> long-lived token
                // (If your app needs persistent tokens, uncomment this)
                /*
                $longResp = \Illuminate\Support\Facades\Http::get('https://graph.threads.net/access_token', [
                    'grant_type'        => 'th_exchange_token',
                    'client_secret'     => $clientSecret,
                    'access_token'      => $tokenJson['access_token'],
                ]);

                if ($longResp->ok()) {
                    $longJson = $longResp->json();
                    if (!empty($longJson['access_token'])) {
                        session(['Threads_AccessToken' => $longJson['access_token']]);
                    }
                }
                */

                // Reload page to continue profile fetch step
                return redirect($redirectUri);
            }

            // ====== STEP 2: We have a token, fetch Threads profile via Threads API (/me) ======
            $accessToken = session('Threads_AccessToken');

            // Threads profile endpoint (NO app secret needed here)
            $profileResp = \Illuminate\Support\Facades\Http::get('https://graph.threads.net/me', [
                'fields'       => 'id,username,threads_profile_picture_url', // keep minimal; add more fields only if supported in your API version
                'access_token' => $accessToken,
            ]);

            if (!$profileResp->ok()) {
                // If token invalid/expired, clear and restart OAuth
                session()->forget('Threads_AccessToken');
                throw new \Exception('Threads /me failed: ' . $profileResp->body());
            }

            $profile = $profileResp->json();

            if (!empty($profile['id'])) {
                $username = $profile['username'] ?? 'threads';

                $result[] = [
                    'id' => $profile['id'],
                    'name' => $username,
                    'username' => $username,
                    'avatar' => $profile['threads_profile_picture_url'] ?? text2img($username),
                    'desc' => $username,
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
