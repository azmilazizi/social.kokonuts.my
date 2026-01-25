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
            ];
        }

        $caption = $payload['caption'] ?? '';

        $attachmentUrl = $payload['medias'] ?? null;
        $mediaType = 'TEXT';
        if (!empty($attachmentUrl)) {
            $mediaType = Media::isVideo($attachmentUrl) ? 'VIDEO' : 'IMAGE';
        }

        $graphVersion   = get_option('threads_graph_version', 'v21.0');
        $baseUrl        = 'https://graph.threads.net/' . $graphVersion;
        $createEndpoint = $baseUrl . '/' . $userId . '/threads';
        $publishEndpoint= $baseUrl . '/' . $userId . '/threads_publish';

        \Log::info('[Threads] endpoints', [
            'create'  => $createEndpoint,
            'publish' => $publishEndpoint,
        ]);

        $createPayload = [
            'access_token' => $accessToken,
        ];

        if (!empty($attachmentUrl)) {
            $createPayload['media_type'] = $mediaType;
            if ($mediaType === 'VIDEO') {
                $createPayload['video_url'] = $attachmentUrl[0];
            } else {
                $createPayload['image_url'] = $attachmentUrl[0];
            }

            if ($caption !== '') {
                $createPayload['text'] = $caption;
            }
        } else {
            if ($caption === '') {
                return [
                    'status' => 0,
                    'message' => 'Threads create failed: text is required when media_type is TEXT.',
                    'media_type' => $mediaType,
                ];
            }
            $createPayload['media_type'] = 'TEXT';
            $createPayload['text'] = $caption;
        }

         \Log::info('[Threads] payload', $createPayload);

        $createResponse = Http::asForm()->timeout(90)->post($createEndpoint, $createPayload);

        \Log::info('[Threads] create response', [
            'status' => $createResponse->status(),
            'body'   => $createResponse->body(),
        ]);


        if (!$createResponse->successful()) {
            return [
                'status' => 0,
                'message' => 'Threads create failed: ' . $createResponse->body(),
                'media_type' => $mediaType,
            ];
        }

        $creationId = $createResponse->json('id');

        if (!$creationId) {
            return [
                'status' => 0,
                'message' => 'Threads create returned no id: ' . $createResponse->body(),
                'media_type' => $mediaType,
            ];
        }

        $publishResponse = null;
        $maxPublishAttempts = (int) get_option('threads_publish_attempts', 3);
        $publishDelaySeconds = (int) get_option('threads_publish_delay_seconds', 2);

        for ($attempt = 1; $attempt <= max(1, $maxPublishAttempts); $attempt++) {
            $publishResponse = Http::asForm()->timeout(90)->post($publishEndpoint, [
                'creation_id'  => $creationId,
                'access_token' => $accessToken,
            ]);

            \Log::info('[Threads] publish response', [
                'status' => $publishResponse->status(),
                'body'   => $publishResponse->body(),
                'creation_id' => $creationId ?? null,
                'attempt' => $attempt,
            ]);

            if ($publishResponse->successful()) {
                break;
            }

            $errorSubcode = $publishResponse->json('error.error_subcode');
            if ((int) $errorSubcode !== 4279009 || $attempt >= $maxPublishAttempts) {
                break;
            }

            if ($publishDelaySeconds > 0) {
                sleep($publishDelaySeconds);
            }
        }

        if (!$publishResponse || !$publishResponse->successful()) {
            return [
                'status' => 0,
                'message' => 'Threads publish failed: ' . ($publishResponse?->body() ?? 'No response'),
                'media_type' => $mediaType,
            ];
        }

        $postId = $publishResponse->json('id');
        $profileUrl = $username ? 'https://www.threads.net/@' . ltrim($username, '@') : null;

        return [
            'status'  => 1,
            'message' => 'Succeeded',
            'id'      => $postId,
            'url'     => $profileUrl,
            'media_type'    => $mediaType,
        ];
    }
}
