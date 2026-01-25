<?php
namespace Modules\AppChannelThreadsUnofficial\Services;

use Illuminate\Support\Facades\Http;
use Media;
class ThreadsUnofficialService
{
    protected ?string $accessToken = null;
    protected ?string $userId = null;
    protected ?string $username = null;

    public function setCredentials(string $accessToken, string $userId, string $username): void
    {
        $this->accessToken = $accessToken;
        $this->userId = $userId;
        $this->username = ltrim($username, '@');
    }

    public function createPost(array $payload): array
    {
        $accessToken = $payload['access_token'] ?? $this->accessToken;
        $userId = $payload['user_id'] ?? $this->userId;
        $username = $payload['username'] ?? $this->username;

        if (empty($accessToken) || empty($userId)) {
            return [
                'status' => 0,
                'message' => __('Threads access token or user ID is missing. Please reconnect your account.'),
                'type' => $payload['type'] ?? 'text',
            ];
        }

        $caption = $payload['caption'] ?? '';
        $link = $payload['link'] ?? null;

        if (!empty($link) && $payload['type'] === 'link') {
            $caption = trim($caption . ' ' . $link);
        }

        $medias = $payload['medias'] ?? [];
        $mediaUrls = array_map(static fn ($media) => Media::url($media), $medias);

        $graphVersion = get_option('threads_graph_version', 'v21.0');
        $baseUrl = 'https://graph.threads.net/' . $graphVersion;
        $createEndpoint = $baseUrl . '/' . $userId . '/threads';
        $publishEndpoint = $baseUrl . '/' . $userId . '/threads_publish';

        $createPayload = [
            'text' => $caption,
            'access_token' => $accessToken,
        ];

        if (!empty($mediaUrls)) {
            $mediaUrl = $mediaUrls[0];

            if (Media::isVideo($mediaUrl)) {
                // TEXT + VIDEO
                $createPayload['media_type'] = 'VIDEO';
                $createPayload['video_url']  = $mediaUrl;
            } else {
                // TEXT + IMAGE
                $createPayload['media_type'] = 'IMAGE';
                $createPayload['image_url']  = $mediaUrl;
            }
        } else {
            // TEXT ONLY
            $createPayload['media_type'] = 'TEXT';
        }


        $createResponse = Http::asForm()->timeout(60)->post($createEndpoint, $createPayload);

        if (!$createResponse->successful()) {
            return [
                'status' => 0,
                'message' => __('Threads create request failed with status :status.', ['status' => $createResponse->status()]),
                'type' => $payload['type'] ?? 'text',
            ];
        }

        $createBody = $createResponse->json() ?? [];
        $creationId = $createBody['id'] ?? null;
        if (empty($creationId)) {
            return [
                'status' => 0,
                'message' => $createBody['message'] ?? __('Threads create request returned an error.'),
                'type' => $payload['type'] ?? 'text',
            ];
        }

        $publishResponse = Http::asForm()->timeout(60)->post($publishEndpoint, [
            'creation_id' => $creationId,
            'access_token' => $accessToken,
        ]);

        if (!$publishResponse->successful()) {
            return [
                'status' => 0,
                'message' => __('Threads publish request failed with status :status.', ['status' => $publishResponse->status()]),
                'type' => $payload['type'] ?? 'text',
            ];
        }

        $publishBody = $publishResponse->json() ?? [];
        $postId = $publishBody['id'] ?? null;
        $profileUrl = $username ? 'https://www.threads.net/@' . $username : null;

        return [
            'status' => 1,
            'message' => $publishBody['message'] ?? __('Succeeded'),
            'id' => $postId,
            'url' => $profileUrl,
            'type' => $payload['type'] ?? 'text',
        ];
    }
}
