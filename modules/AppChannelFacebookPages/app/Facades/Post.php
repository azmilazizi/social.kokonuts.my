<?php

namespace Modules\AppChannelFacebookPages\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;
use JanuSoftware\Facebook\Facebook;
use Media;
use getID3;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Post extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    /**
     * Validate post data for Facebook-specific requirements.
     */
    public static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, false);
        $medias = $data->medias ?? [];
        $options = $data->options ?? null;

        if ($options && ($options->fb_type ?? null) === 'reels') {
            if (empty($medias) || !Media::isVideo($medias[0])) {
                $errors[] = __("Facebook Reels only supports posting videos (3–90 seconds).");
            } else {
                $videoPath = Media::path($medias[0]);
                if (file_exists($videoPath)) {
                    $getID3 = new getID3;
                    $fileInfo = $getID3->analyze($videoPath);
                    $duration = $fileInfo['playtime_seconds'] ?? 0;
                    if ($duration < 3 || $duration > 90) {
                        $errors[] = __("Facebook Reels only supports posting videos (3–90 seconds).");
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Main post entry point.
     */
    public static function post($post)
    {
        $FB = new Facebook([
            'app_id'              => get_option("facebook_app_id", ""),
            'app_secret'          => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_graph_version", "v21.0"),
        ]);

        $data = json_decode($post->data);
        $medias = $data->medias ?? [];
        $endpoint = "/" . $post->account->pid . "/";
        $caption = trim(spintax($data->caption ?? ''));
        $postType = $data->options->fb_type ?? "default";

        try {
            if ($post->account->login_type != 1) {
                return [
                    "status" => 0,
                    "message" => __("Unsupported account login type"),
                    "type" => $post->type,
                ];
            }

            return match ($postType) {
                'reels' => self::handleReels($FB, $post, $data, $medias, $endpoint, $caption),
                default => self::handleDefault($FB, $post, $data, $medias, $endpoint, $caption),
            };
        } catch (\Exception $e) {
            if ($e->getCode() == 190) {
                DB::table("accounts")
                    ->where("id", $post->account->id)
                    ->update(["status" => 0]);
            }

            $response = method_exists($e, 'getResponseData') ? $e->getResponseData() : null;
            if (is_array($response['error'] ?? null)) {
                $fbErr = $response['error'];
                $msg = $fbErr['error_user_msg'] ?? $fbErr['message'] ?? $e->getMessage();
                $title = $fbErr['error_user_title'] ?? null;

                return [
                    "status" => 0,
                    "message" => $title ? "$title: $msg" : $msg,
                    "type" => $post->type,
                ];
            }

            return [
                "status" => 0,
                "message" => $e->getMessage(),
                "type" => $post->type,
            ];
        }
    }

    /**
     * Handle Facebook Reels posts.
     */
    protected static function handleReels($FB, $post, $data, $medias, $endpoint, $caption)
    {
        switch ($post->type) {
            case 'media':
                if (empty($medias) || !Media::isVideo($medias[0])) {
                    return [
                        "status" => 0,
                        "message" => __("Facebook Reels only support video posts."),
                        "type" => $post->type,
                    ];
                }
                $uploadParams = [
                    "upload_phase" => "start",
                ];
                if ($caption !== '') {
                    $uploadParams['description'] = $caption;
                }
                $uploadSession = $FB->post($endpoint . 'video_reels', $uploadParams, $post->account->token)
                    ->getDecodedBody();

                if (empty($uploadSession['video_id'])) {
                    return [
                        "status" => 0,
                        "message" => __("Could not create upload session for Reels."),
                        "type" => $post->type,
                    ];
                }
                return self::completeReelsUpload($FB, $post, $uploadSession, $caption, Media::url($medias[0]), $endpoint);

            case 'link':
                if (empty($medias) || !Media::isVideo($medias[0])) {
                    return [
                        "status" => 0,
                        "message" => __("Facebook Reels only support video posts."),
                        "type" => $post->type,
                    ];
                }
                $uploadParams = [
                    "upload_phase" => "start",
                ];
                if ($caption !== '') {
                    $uploadParams['description'] = $caption;
                }
                $uploadSession = $FB->post($endpoint . 'video_reels', $uploadParams, $post->account->token)
                    ->getDecodedBody();

                if (empty($uploadSession['video_id'])) {
                    return [
                        "status" => 0,
                        "message" => __("Could not create upload session for Reels."),
                        "type" => $post->type,
                    ];
                }

                return self::completeReelsUpload($FB, $post, $uploadSession, $caption, Media::url($medias[0]), $endpoint, $data->link);
            case 'text':
                return [
                    "status" => 0,
                    "message" => __("Facebook Reels do not support text-only posts."),
                    "type" => $post->type,
                ];
            default:
                return [
                    "status" => 0,
                    "message" => __("Unknown Reels post type."),
                    "type" => $post->type,
                ];
        }
    }

    /**
     * Complete the upload for a Reels video.
     */
    protected static function completeReelsUpload($FB, $post, $uploadSession, $caption, $mediaUrl, $endpoint, $thumbnail_url = null)
    {
        $videoId = $uploadSession['video_id'];
        $uploadUrl = $uploadSession['upload_url'] ?? null;

        if (empty($videoId) || empty($uploadUrl)) {
            return [
                "status" => 0,
                "message" => __("Could not create upload session for Reels (missing video_id or upload_url)."),
                "type" => $post->type,
            ];
        }

        $transferResp = Http::withHeaders([
            'Authorization' => 'OAuth ' . $post->account->token,
            'file_url'      => $mediaUrl,
        ])->timeout(300)->post($uploadUrl);

        if (!$transferResp->successful()) {
            return [
                "status" => 0,
                "message" => "Reels transfer failed: " . $transferResp->body(),
                "type" => $post->type,
            ];
        }

        $transferJson = $transferResp->json() ?? [];
        if (isset($transferJson['success']) && (int)$transferJson['success'] !== 1) {
            return [
                "status" => 0,
                "message" => "Reels transfer not successful: " . $transferResp->body(),
                "type" => $post->type,
            ];
        }

        if (empty($transferJson['success']) || $transferJson['success'] != 1) {
            return [
                "status" => 0,
                "message" => __("Could not transfer Reels upload."),
                "type" => $post->type,
            ];
        }

        $finishParams = [
            'upload_phase' => 'finish',
            'video_state' => 'PUBLISHED',
            'video_id' => $videoId,
        ];
        if ($caption !== '') {
            $finishParams['description'] = $caption;
        }
        $finishResponse = $FB->post($endpoint . 'video_reels', $finishParams, $post->account->token)->getDecodedBody();

        if (empty($finishResponse['success']) || $finishResponse['success'] != 1) {
            return [
                "status" => 0,
                "message" => __("Could not finish Reels upload."),
                "type" => $post->type,
            ];
        }

        $maxAttempts = 10;
        $pollDelaySeconds = 3;
        $phaseStatus = function ($phase): string {
            // Sometimes Meta returns these as objects/arrays like: { "status": "complete" }
            // so normalize to a lowercase string safely.
            if (is_string($phase)) {
                return strtolower($phase);
            }

            if (is_array($phase)) {
                $v = $phase['status'] ?? '';
                return is_string($v) ? strtolower($v) : '';
            }

            if (is_object($phase)) {
                $v = $phase->status ?? '';
                return is_string($v) ? strtolower($v) : '';
            }

            return '';
        };

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $statusResponse = $FB->get("/$videoId?fields=status", $post->account->token)->getDecodedBody();
            $status = $statusResponse['status'] ?? [];

            // If status comes back weird, don't crash
            if (!is_array($status)) $status = [];

            // Meta can return errors under status.error sometimes
            $error = $status['error'] ?? null;
            if ($error) {
                $errorMessage = is_array($error) ? ($error['message'] ?? "Reels processing failed.") : (string)$error;
                return [
                    "status" => 0,
                    "message" => $errorMessage,
                    "type" => $post->type,
                ];
            }

            $processingPhase = $phaseStatus($status['processing_phase'] ?? null);
            $publishingPhase = $phaseStatus($status['publishing_phase'] ?? null); // NOTE: publishing_phase (bukan publish_phase)
            $videoStatus     = is_string($status['video_status'] ?? '') ? strtolower($status['video_status']) : '';

            if (in_array($videoStatus, ['ready', 'published'], true)) {
                // === FACEBOOK REELS THUMBNAIL HOOK ===
                try {
                    if ($thumbnail_url) {
                        \Log::warning('FB Reels: resolving thumbnail', [
                            'video_id' => $videoId,
                            'thumb_url' => $thumbnail_url,
                        ]);

                        $thumbnail = self::resolveVideoThumbnail($thumbnail_url);

                        if ($thumbnail) {
                            // small delay to let FB stabilize
                            sleep(2);

                            self::uploadVideoThumbnail($post, $videoId, $thumbnail);

                            // cleanup local file
                            if (!empty($thumbnail['path']) && file_exists($thumbnail['path'])) {
                                @unlink($thumbnail['path']);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('FB Reels thumbnail failed (non-blocking)', [
                        'video_id' => $videoId,
                        'error' => $e->getMessage(),
                    ]);
                }

                return [
                    "status" => 1,
                    "message" => __("Success"),
                    "id" => $videoId,
                    "url" => "https://www.facebook.com/reel/",
                    "type" => "reels",
                ];
            }

            // fallback: phase-based success
            $isProcessed = in_array($processingPhase, ['complete'], true);
            $isPublished = in_array($publishingPhase, ['complete'], true);

            if ($isProcessed && $isPublished) {
                return [
                    "status" => 1,
                    "message" => __("Success"),
                    "id" => $videoId,
                    "url" => "https://www.facebook.com/reel/",
                    "type" => "reels",
                ];
            }

            sleep($pollDelaySeconds);
        }


        return [
            "status" => 0,
            "message" => __("Reels processing timed out."),
            "type" => "reels",
        ];
    }

    /**
     * Handle default Facebook post types (media, link, text).
     */
    protected static function handleDefault($FB, $post, $data, $medias, $endpoint, $caption)
    {
        [$endpoint, $params] = self::handleDefaultPost($FB, $post, $data, $medias, $caption, $endpoint);

        if (empty($endpoint) || !is_string($endpoint)) {
            return [
                "status" => 0,
                "message" => __("Media not found or unsupported media type."),
                "type" => $post->type,
            ];
        }

        $response = $FB->post($endpoint, $params, $post->account->token)->getDecodedBody();
        $postId = $response['id'] ?? null;

        
        if ($postId && $post->type === 'link' && !empty($medias) && Media::isVideo($medias[0])) {
            $thumbnail = self::resolveVideoThumbnail($data->link);
            
            if (!empty($thumbnail)) {
                sleep(2);
                $videoNodeId = self::resolveVideoIdFromPostId($post, $postId);

                if ($videoNodeId) {
                    self::uploadVideoThumbnail($post, $videoNodeId, $thumbnail);
                } else {
                    \Log::warning('Could not resolve video node id from post id', ['post_id' => $postId]);
                }

                // DELETE local thumbnail file
                if (!empty($thumbnail['path']) && file_exists($thumbnail['path'])) {
                    @unlink($thumbnail['path']);
                }
            }
        }

        return [
            "status" => 1,
            "message" => __("Success"),
            "id" => $postId,
            "url" => $postId ? "https://fb.com/$postId" : null,
            "type" => $post->type,
        ];
    }

    /**
     * Map post type to Facebook endpoint/parameters.
     */
    protected static function handleDefaultPost($FB, $post, $data, $medias, $caption, $endpoint)
    {
        $params = [];
        switch ($post->type) {
            case 'media':
                return self::handleMediaPost($FB, $post, $medias, $caption, $endpoint);
            case 'link':
                return [$endpoint . "feed", [
                    'message' => $caption,
                    'link'    => $data->link,
                ]];
            case 'text':
                return [$endpoint . "feed", [
                    'message' => $caption,
                ]];
            default:
                return [null, []];
        }
    }

    /**
     * Handle media uploads for single/multiple images or videos.
     */
    protected static function handleMediaPost($FB, $post, $medias, $caption, $endpoint)
    {
        if (!empty($medias) && Media::isVideo($medias[0])) {
            return [$endpoint . "videos", [
                'description' => $caption,
                'file_url' => Media::url($medias[0]),
            ]];
        }

        if (count($medias) === 1) {
            $media = $medias[0];
            if (Media::isImg($media)) {
                return [$endpoint . "photos", [
                    'message' => $caption,
                    'url' => Media::url($media),
                ]];
            }
            if (Media::isVideo($media)) {
                return [$endpoint . "videos", [
                    'description' => $caption,
                    'file_url' => Media::url($media),
                ]];
            }
            return [null, []];
        }

        // Multiple images
        $mediaIds = [];
        $count = 0;
        foreach ($medias as $media) {
            if (Media::isImg($media)) {
                $upload = $FB->post($endpoint . 'photos', [
                    'url' => Media::url($media),
                    'published' => false,
                ], $post->account->token)->getDecodedBody();
                if (!empty($upload['id'])) {
                    $mediaIds['attached_media[' . $count . ']'] = '{"media_fbid":"' . $upload['id'] . '"}';
                    $count++;
                }
            }
        }
        $params = ['message' => $caption] + $mediaIds;
        return [$endpoint . "feed", $params];
    }

    protected static function resolveVideoIdFromPostId($post, string $postId): ?string
    {
        $graphVersion = get_option("facebook_graph_version", "v21.0");
        $url = "https://graph.facebook.com/{$graphVersion}/{$postId}";

        $resp = Http::timeout(30)->get($url, [
            'fields' => 'attachments{media_type,media}',
            'access_token' => $post->account->token,
        ]);

        if (!$resp->successful()) {
            \Log::warning('Resolve video id failed', [
                'post_id' => $postId,
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);
            return null;
        }

        $body = $resp->json();

        $attachments = $body['attachments']['data'] ?? [];
        foreach ($attachments as $att) {
            // media_type examples: "video", "photo", etc
            $mediaType = $att['media_type'] ?? null;
            $mediaId   = $att['media']['id'] ?? null;

            if ($mediaType && stripos($mediaType, 'video') !== false && $mediaId) {
                return (string) $mediaId; // ✅ this is the Video node id
            }
        }

        return null;
    }


    protected static function resolveVideoThumbnail(string $publicUrl): ?array
    {
        $tmpIn = null;

        try {
            // Validate URL
            if (!filter_var($publicUrl, FILTER_VALIDATE_URL)) {
                return null;
            }

            // Download image
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 15,
                    'follow_location' => true,
                    'user_agent' => 'KokonutsSocial/1.0',
                ],
            ]);

            $raw = @file_get_contents($publicUrl, false, $ctx);
            if (!$raw) {
                return null;
            }

            // Temp input file
            $tmpIn = sys_get_temp_dir() . '/thumb_in_' . uniqid();
            file_put_contents($tmpIn, $raw);

            // Detect image
            $info = @getimagesize($tmpIn);
            if (!$info || empty($info['mime'])) {
                return null;
            }

            $mime   = $info['mime'];
            $width  = (int) ($info[0] ?? 0);
            $height = (int) ($info[1] ?? 0);

            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                return null;
            }

            // Create GD resource
            switch ($mime) {
                case 'image/jpeg':
                    $im = imagecreatefromjpeg($tmpIn);
                    break;
                case 'image/png':
                    $im = imagecreatefrompng($tmpIn);
                    break;
                case 'image/webp':
                    if (!function_exists('imagecreatefromwebp')) {
                        return null;
                    }
                    $im = imagecreatefromwebp($tmpIn);
                    break;
                default:
                    return null;
            }

            if (!$im) {
                return null;
            }

            // Hardcoded output directory
            $relativePath = 'files/meta-thumbnails/thumb_' . uniqid() . '.jpg';
            $outputPath  = storage_path('app/public/' . $relativePath);

            if (!is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0775, true);
            }

            // Convert to JPEG (quality hardcoded to 85)
            imagejpeg($im, $outputPath, 85);
            imagedestroy($im);

            // Read final info
            $finalInfo = @getimagesize($outputPath);
            $bytes = @filesize($outputPath);

            // Public URL
            $publicThumbUrl = rtrim(config('app.url'), '/') . '/storage/' . $relativePath;

            return [
                'path'      => $outputPath,       // for FB thumbnail upload (CURLFile)
                'relative'  => $relativePath,     // storage relative
                'url'       => $publicThumbUrl,   // for IG cover_url
                'width'     => (int) ($finalInfo[0] ?? $width),
                'height'    => (int) ($finalInfo[1] ?? $height),
                'mime'      => 'image/jpeg',
                'bytes'     => $bytes,
                'source'    => $publicUrl,
            ];
        } catch (\Throwable $e) {
            return null;
        } finally {
            if ($tmpIn && file_exists($tmpIn)) {
                @unlink($tmpIn);
            }
        }
    }

    protected static function uploadVideoThumbnail($post, $videoId, $thumbnail)
    {
        $payload = self::buildThumbnailPayload($thumbnail);
        if (!$payload) {
            return;
        }

        $graphVersion = get_option("facebook_graph_version", "v21.0");
        $uploadUrl = "https://graph.facebook.com/{$graphVersion}/{$videoId}/thumbnails";

        try {
            $response = Http::timeout(60)
                ->attach('source', $payload['contents'], $payload['filename'])
                ->post($uploadUrl, [
                    'access_token' => $post->account->token,
                    'is_preferred' => 'true',
                ]);

            \Log::info('Facebook thumbnail upload response', [
                'video_id' => $videoId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                Log::warning('Facebook thumbnail upload failed', [
                    'video_id' => $videoId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Facebook thumbnail upload exception', [
                'video_id' => $videoId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // protected static function buildThumbnailPayload($thumbnail): ?array
    // {
    //     if (!$thumbnail) {
    //         return null;
    //     }

    //     if (filter_var($thumbnail, FILTER_VALIDATE_URL)) {
    //         $response = Http::timeout(30)->get($thumbnail);
    //         if (!$response->successful()) {
    //             return null;
    //         }

    //         $path = parse_url($thumbnail, PHP_URL_PATH) ?: '';
    //         $filename = basename($path) ?: 'thumbnail.jpg';

    //         return [
    //             'contents' => $response->body(),
    //             'filename' => $filename,
    //         ];
    //     }

    //     $path = Media::path($thumbnail);
    //     if ($path && file_exists($path)) {
    //         return [
    //             'contents' => file_get_contents($path),
    //             'filename' => basename($path),
    //         ];
    //     }

    //     $url = Media::url($thumbnail);
    //     if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
    //         $response = Http::timeout(30)->get($url);
    //         if (!$response->successful()) {
    //             return null;
    //         }

    //         $path = parse_url($url, PHP_URL_PATH) ?: '';
    //         $filename = basename($path) ?: 'thumbnail.jpg';

    //         return [
    //             'contents' => $response->body(),
    //             'filename' => $filename,
    //         ];
    //     }

    //     return null;
    // }

    protected static function buildThumbnailPayload($thumbnail)
    {
        if (
            empty($thumbnail['path']) ||
            !file_exists($thumbnail['path']) ||
            !is_readable($thumbnail['path'])
        ) {
            return null;
        }

        return [
            'contents' => file_get_contents($thumbnail['path']),
            'filename' => basename($thumbnail['path']),
        ];
    }
}
