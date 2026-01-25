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

        $caption = trim($payload['caption'] ?? '');

        // medias can be array (Stackposts) - get first item
        $medias = $payload['medias'] ?? [];
        $attachmentUrl = null;

        if (is_array($medias) && !empty($medias)) {
            $attachmentUrl = $medias[0];
            // if stored as Media object/id, convert to url
            // if it's already a full URL, Media::url should return same or you can skip
            $attachmentUrl = Media::url($attachmentUrl);
        } elseif (is_string($medias) && $medias !== '') {
            // just in case someone passes a single string
            $attachmentUrl = Media::url($medias);
        }

        $mediaType = 'TEXT';
        if (!empty($attachmentUrl)) {
            $mediaType = Media::isVideo($attachmentUrl) ? 'VIDEO' : 'IMAGE';
        }

        // ✅ Threads API version MUST be v1.0
        $graphVersion = get_option('threads_graph_version', 'v1.0');
        $baseUrl = "https://graph.threads.net/{$graphVersion}";
        $createEndpoint  = "{$baseUrl}/{$userId}/threads";
        $publishEndpoint = "{$baseUrl}/{$userId}/threads_publish";

        \Log::info('[Threads] endpoints', [
            'create'  => $createEndpoint,
            'publish' => $publishEndpoint,
        ]);

        // Build create payload
        $createPayload = [
            'access_token' => $accessToken,
            'media_type'   => $mediaType,
        ];

        if ($mediaType === 'TEXT') {
            if ($caption === '') {
                return [
                    'status' => 0,
                    'message' => 'Threads create failed: text is required for TEXT posts.',
                    'media_type' => $mediaType,
                ];
            }
            $createPayload['text'] = $caption;
        } else {
            // media post can have caption
            if ($caption !== '') {
                $createPayload['text'] = $caption;
            }

            if ($mediaType === 'VIDEO') {
                $createPayload['video_url'] = $attachmentUrl;
            } else {
                $createPayload['image_url'] = $attachmentUrl;
            }
        }

        \Log::info('[Threads] create payload', $createPayload);

        // STEP 1: Create container
        $createResponse = Http::asForm()->timeout(120)->post($createEndpoint, $createPayload);

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

        // ✅ STEP 2: Wait/poll for media container to be ready (IMPORTANT for VIDEO/IMAGE)
        if ($mediaType !== 'TEXT') {
            $statusEndpoint = "{$baseUrl}/{$creationId}";
            $maxWaitSeconds = (int) get_option('threads_container_max_wait', 90);
            $sleepSeconds   = (int) get_option('threads_container_poll_sleep', 3);

            $deadline = time() + max(10, $maxWaitSeconds);
            $lastStatus = null;

            while (time() < $deadline) {
                $statusResp = Http::timeout(30)->get($statusEndpoint, [
                    'fields' => 'status,error_message',
                    'access_token' => $accessToken,
                ]);

                \Log::info('[Threads] container status', [
                    'creation_id' => $creationId,
                    'http' => $statusResp->status(),
                    'body' => $statusResp->body(),
                ]);

                if ($statusResp->ok()) {
                    $lastStatus = $statusResp->json('status');

                    if (in_array($lastStatus, ['FINISHED', 'READY'], true)) {
                        break;
                    }

                    if (in_array($lastStatus, ['ERROR', 'FAILED'], true)) {
                        return [
                            'status' => 0,
                            'message' => 'Threads media processing failed: ' . ($statusResp->json('error_message') ?? $statusResp->body()),
                            'media_type' => $mediaType,
                        ];
                    }
                }

                sleep(max(1, $sleepSeconds));
            }

            if (!in_array($lastStatus, ['FINISHED', 'READY'], true)) {
                return [
                    'status' => 0,
                    'message' => "Threads media not ready after waiting ({$maxWaitSeconds}s). Last status: " . ($lastStatus ?? 'unknown'),
                    'media_type' => $mediaType,
                ];
            }
        }

        // STEP 3: Publish
        $publishResponse = Http::asForm()->timeout(90)->post($publishEndpoint, [
            'creation_id'  => $creationId,
            'access_token' => $accessToken,
        ]);

        \Log::info('[Threads] publish response', [
            'status' => $publishResponse->status(),
            'body'   => $publishResponse->body(),
            'creation_id' => $creationId,
        ]);

        if (!$publishResponse->successful()) {
            return [
                'status' => 0,
                'message' => 'Threads publish failed: ' . $publishResponse->body(),
                'media_type' => $mediaType,
            ];
        }

        $postId = $publishResponse->json('id');
        $profileUrl = $username ? 'https://www.threads.net/@' . ltrim($username, '@') : null;

        return [
            'status' => 1,
            'message' => 'Succeeded',
            'id' => $postId,
            'url' => $profileUrl,
            'media_type' => $mediaType,
        ];
    }

}
