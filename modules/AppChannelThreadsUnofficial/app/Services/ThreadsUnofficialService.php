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
        $userId      = $payload['user_id'] ?? $this->userId;
        $username    = $payload['username'] ?? $this->username;

        if (empty($accessToken) || empty($userId)) {
            return [
                'status' => 0,
                'message' => 'Threads access token or user ID is missing. Please reconnect.',
                'type' => $payload['type'] ?? 'text',
            ];
        }

        $caption = $payload['caption'] ?? '';
        $link    = $payload['link'] ?? null;

        if (!empty($link) && ($payload['type'] ?? '') === 'link') {
            $caption = trim($caption . ' ' . $link);
        }

        $medias    = $payload['medias'] ?? [];
        $mediaUrls = array_map(static fn ($media) => Media::url($media), $medias);

        \Log::info('[Threads] payload', [
            'type'      => $payload['type'] ?? null,
            'user_id'   => $userId,
            'username'  => $username,
            'caption'   => $caption,
            'medias'    => $medias,
            'mediaUrls' => $mediaUrls,
        ]);

        $graphVersion   = get_option('threads_graph_version', 'v21.0');
        $baseUrl        = 'https://graph.threads.net/' . $graphVersion;
        $createEndpoint = $baseUrl . '/' . $userId . '/threads';
        $publishEndpoint= $baseUrl . '/' . $userId . '/threads_publish';

        \Log::info('[Threads] endpoints', [
            'create'  => $createEndpoint,
            'publish' => $publishEndpoint,
        ]);

        $createPayload = [
            'text'         => $caption,
            'access_token' => $accessToken,
        ];

        if (!empty($mediaUrls)) {
            $mediaUrl = $mediaUrls[0];

            // Validate media URL is reachable (super helpful)
            try {
                $head = Http::timeout(15)->head($mediaUrl);
                if (!$head->successful()) {
                    return [
                        'status' => 0,
                        'message' => "Media URL not reachable ({$head->status()}): {$mediaUrl}",
                        'type' => $payload['type'] ?? 'text',
                    ];
                }
            } catch (\Throwable $e) {
                return [
                    'status' => 0,
                    'message' => "Media URL HEAD failed: {$mediaUrl} | " . $e->getMessage(),
                    'type' => $payload['type'] ?? 'text',
                ];
            }

            if (Media::isVideo($mediaUrl)) {
                $createPayload['media_type'] = 'VIDEO';
                $createPayload['video_url']  = $mediaUrl;
            } else {
                $createPayload['media_type'] = 'IMAGE';
                $createPayload['image_url']  = $mediaUrl;
            }
        } else {
            $createPayload['media_type'] = 'TEXT';
        }

        $createResponse = Http::asForm()->timeout(90)->post($createEndpoint, $createPayload);

        \Log::info('[Threads] create response', [
            'status' => $createResponse->status(),
            'body'   => $createResponse->body(),
        ]);


        if (!$createResponse->successful()) {
            return [
                'status' => 0,
                'message' => 'Threads create failed: ' . $createResponse->body(),
                'type' => $payload['type'] ?? 'text',
            ];
        }

        $creationId = $createResponse->json('id');

        if (!$creationId) {
            return [
                'status' => 0,
                'message' => 'Threads create returned no id: ' . $createResponse->body(),
                'type' => $payload['type'] ?? 'text',
            ];
        }

        $publishResponse = Http::asForm()->timeout(90)->post($publishEndpoint, [
            'creation_id'  => $creationId,
            'access_token' => $accessToken,
        ]);

        \Log::info('[Threads] publish response', [
            'status' => $publishResponse->status(),
            'body'   => $publishResponse->body(),
            'creation_id' => $creationId ?? null,
        ]);

        if (!$publishResponse->successful()) {
            return [
                'status' => 0,
                'message' => 'Threads publish failed: ' . $publishResponse->body(),
                'type' => $payload['type'] ?? 'text',
            ];
        }

        $postId = $publishResponse->json('id');
        $profileUrl = $username ? 'https://www.threads.net/@' . ltrim($username, '@') : null;

        return [
            'status'  => 1,
            'message' => 'Succeeded',
            'id'      => $postId,
            'url'     => $profileUrl,
            'type'    => $payload['type'] ?? 'text',
        ];
    }
}
