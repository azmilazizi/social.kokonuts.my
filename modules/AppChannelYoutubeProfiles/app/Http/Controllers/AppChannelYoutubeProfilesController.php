<?php

namespace Modules\AppChannelYoutubeProfiles\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Google\Service\YouTube;
use Google\Service\Exception as GoogleServiceException;

class AppChannelYoutubeProfilesController extends Controller
{
    protected GoogleClient $client;
    protected YouTube $youtube;
    protected string $callbackUrl;

    public function __construct()
    {
        \Access::check('appchannels.' . module('key'));

        $clientId = get_option("youtube_client_id", "");
        $clientSecret = get_option("youtube_client_secret", "");
        $this->callbackUrl = module_url();

        if (!$clientId || !$clientSecret) {
            \Access::deny(__('To use YouTube, you must first configure the client ID and client secret.'));
        }

        $this->client = new GoogleClient();
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setRedirectUri($this->callbackUrl);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setIncludeGrantedScopes(true);

        $scopes = array_filter(array_map('trim', explode(',', get_option('youtube_scopes', ''))));
        if (empty($scopes)) {
            $scopes = [
                YouTube::YOUTUBE_READONLY,
                YouTube::YOUTUBE_UPLOAD,
            ];
        }
        $this->client->setScopes($scopes);

        $this->youtube = new YouTube($this->client);
    }

    public function index(Request $request)
    {
        $result = [];

        try {
            if (!session("Youtube_AccessToken")) {
                if (!$request->code) {
                    return redirect(module_url("oauth"));
                }

                $token = $this->client->fetchAccessTokenWithAuthCode($request->code);

                if (!empty($token['access_token'])) {
                    session(['Youtube_AccessToken' => $token]);
                }

                return redirect($this->callbackUrl);
            }

            $accessToken = session("Youtube_AccessToken");
            $this->client->setAccessToken($accessToken);

            if ($this->client->isAccessTokenExpired() && !empty($accessToken['refresh_token'])) {
                $refreshed = $this->client->fetchAccessTokenWithRefreshToken($accessToken['refresh_token']);
                if (!empty($refreshed['access_token'])) {
                    $accessToken = array_merge($accessToken, $refreshed);
                    session(['Youtube_AccessToken' => $accessToken]);
                }
            }

            $channelsResponse = $this->youtube->channels->listChannels('snippet', [
                'mine' => true,
                'maxResults' => 50,
            ]);

            foreach ($channelsResponse->getItems() as $channel) {
                $snippet = $channel->getSnippet();
                $thumbnail = $snippet->getThumbnails()->getDefault();

                $result[] = [
                    'id' => $channel->getId(),
                    'name' => $snippet->getTitle(),
                    'avatar' => $thumbnail ? $thumbnail->getUrl() : text2img($snippet->getTitle()),
                    'desc' => __('Channel'),
                    'link' => 'https://www.youtube.com/channel/' . $channel->getId(),
                    'oauth' => $accessToken,
                    'module' => $request->module['module_name'],
                    'reconnect_url' => $request->module['uri']."/oauth",
                    'social_network' => 'youtube',
                    'category' => 'channel',
                    'login_type' => 1,
                    'can_post' => 1,
                    'data' => '',
                    'proxy' => 0,
                ];
            }

            $channels = [
                'status' => $result ? 1 : 0,
                'message' => $result ? __('Succeeded') : __('No channel to add'),
            ];
        } catch (GoogleServiceException $e) {
            $channels = [
                'status' => 0,
                'message' => $e->getMessage(),
            ];
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
            'oauth' => session("Youtube_AccessToken")
        ]);

        session(['channels' => $channels]);
        return redirect(url_app("channels/add"));
    }

    public function oauth(Request $request)
    {
        $request->session()->forget('Youtube_AccessToken');
        $loginUrl = $this->client->createAuthUrl();
        return redirect($loginUrl);
    }

    public function settings()
    {
        return view('appchannelyoutubeprofiles::settings');
    }
}
