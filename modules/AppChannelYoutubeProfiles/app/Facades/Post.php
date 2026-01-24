<?php

namespace Modules\AppChannelYoutubeProfiles\Facades;

use Google\Client as GoogleClient;
use Google\Service\YouTube;
use Google\Service\YouTube\Video;
use Google\Service\YouTube\VideoSnippet;
use Google\Service\YouTube\VideoStatus;
use Google\Http\MediaFileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Modules\AppChannels\Models\Accounts;
use Media;

class Post extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    protected static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, false);
        $medias = $data->medias ?? [];

        if (empty($medias)) {
            $errors[] = __("YouTube: Please select a video.");
        } else {
            $media = Media::url($medias[0]);
            if (!Media::isVideo($media)) {
                $errors[] = __("YouTube only supports video uploads.");
            }
        }

        $caption = $data->caption ?? '';
        if (trim($caption) === '') {
            $errors[] = __("YouTube: Please enter a title or description.");
        }

        return $errors;
    }

    protected static function post($post)
    {
        $account = $post->account;
        $tokenInfo = json_decode($account->token, true) ?? [];
        $client = self::buildClient($tokenInfo);

        if ($client->isAccessTokenExpired()) {
            if (!empty($tokenInfo['refresh_token'])) {
                $client->fetchAccessTokenWithRefreshToken($tokenInfo['refresh_token']);
                $updatedToken = $client->getAccessToken();
                $updatedToken['refresh_token'] = $tokenInfo['refresh_token'];
                Accounts::where("id", $account->id)->update(["token" => json_encode($updatedToken)]);
            } else {
                Accounts::where("id", $account->id)->update(["status" => 0]);
                return [
                    "status" => "error",
                    "message" => __("YouTube session expired"),
                    "type" => $post->type,
                ];
            }
        }

        $errors = self::validator($post);
        if (!empty($errors)) {
            return [
                "status" => "error",
                "message" => implode(', ', $errors),
                "type" => $post->type,
            ];
        }

        $data = json_decode($post->data, false);
        $medias = $data->medias ?? [];
        $caption = spintax($data->caption ?? '');
        $title = $data->title ?? Str::limit(trim($caption), 95, '');
        $description = $caption;
        $options = (array) ($data->options ?? []);
        $youtubeType = $options['yt_type'] ?? 'video';

        if ($youtubeType === 'shorts') {
            $shortsTag = '#shorts';
            $normalizedDescription = Str::lower($description);

            if ($description === '') {
                $description = $shortsTag;
            } elseif (!Str::contains($normalizedDescription, $shortsTag)) {
                $description = trim($description . ' ' . $shortsTag);
            }
        }

        [$filePath, $temporaryFile] = self::resolveVideoPath($medias[0] ?? '');
        if (!$filePath || !file_exists($filePath)) {
            return [
                "status" => 0,
                "message" => __("YouTube: Video file not found."),
                "type" => $post->type,
            ];
        }

        try {
            $youtube = new YouTube($client);
            $video = new Video();
            $snippet = new VideoSnippet();
            $snippet->setTitle($title ?: __('Untitled Video'));
            $snippet->setDescription($description);
            $snippet->setCategoryId((string) get_option('youtube_category_id', '22'));

            $status = new VideoStatus();
            $status->setPrivacyStatus(get_option('youtube_privacy', 'unlisted'));

            $video->setSnippet($snippet);
            $video->setStatus($status);

            $client->setDefer(true);
            $insertRequest = $youtube->videos->insert('snippet,status', $video);

            $media = new MediaFileUpload(
                $client,
                $insertRequest,
                'video/*',
                null,
                true,
                1 * 1024 * 1024
            );
            $media->setFileSize(filesize($filePath));

            $status = false;
            $handle = fopen($filePath, 'rb');
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, 1 * 1024 * 1024);
                $status = $media->nextChunk($chunk);
            }
            fclose($handle);
            $client->setDefer(false);

            if ($status instanceof Video) {
                return [
                    "status" => 1,
                    "message" => __("Successfully posted to YouTube"),
                    "id" => $status->getId(),
                    "url" => 'https://www.youtube.com/watch?v=' . $status->getId(),
                    "type" => $post->type,
                ];
            }

            return [
                "status" => 0,
                "message" => __("YouTube upload failed."),
                "type" => $post->type,
            ];
        } catch (\Exception $e) {
            return [
                "status" => 0,
                "message" => __("YouTube error: ") . $e->getMessage(),
                "type" => $post->type,
            ];
        } finally {
            if (!empty($temporaryFile) && file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }

    protected static function buildClient(array $tokenInfo): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(get_option("youtube_client_id", ""));
        $client->setClientSecret(get_option("youtube_client_secret", ""));
        $client->setRedirectUri(module_url());
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setIncludeGrantedScopes(true);
        $client->setScopes([
            YouTube::YOUTUBE_READONLY,
            YouTube::YOUTUBE_UPLOAD,
        ]);

        if (!empty($tokenInfo)) {
            $client->setAccessToken($tokenInfo);
        }

        return $client;
    }

    protected static function resolveVideoPath(string $mediaPath): array
    {
        if ($mediaPath === '') {
            return [null, false];
        }

        $localPath = Media::path($mediaPath);
        if ($localPath && file_exists($localPath)) {
            return [$localPath, false];
        }

        $defaultDisk = config('filesystems.default', 'public');
        $storagePath = self::normalizeStoragePath($mediaPath);
        if ($storagePath !== '' && Storage::disk($defaultDisk)->exists($storagePath)) {
            $tempPath = self::createTempPath();
            if ($tempPath && self::streamToTemp(Storage::disk($defaultDisk)->readStream($storagePath), $tempPath)) {
                return [$tempPath, true];
            }
        }

        $sourceUrl = filter_var($mediaPath, FILTER_VALIDATE_URL) ? $mediaPath : Media::url($mediaPath);
        if ($sourceUrl && filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
            $tempPath = self::createTempPath();
            if ($tempPath && self::streamToTemp(@fopen($sourceUrl, 'rb'), $tempPath)) {
                return [$tempPath, true];
            }
        }

        return [null, false];
    }

    protected static function normalizeStoragePath(string $mediaPath): string
    {
        if ($mediaPath === '') {
            return '';
        }

        if (!filter_var($mediaPath, FILTER_VALIDATE_URL)) {
            return ltrim($mediaPath, '/');
        }

        $storageBase = rtrim(url('storage/'), '/');
        if (str_starts_with($mediaPath, $storageBase)) {
            return ltrim(Str::after($mediaPath, $storageBase), '/');
        }

        $path = parse_url($mediaPath, PHP_URL_PATH) ?? '';
        return ltrim($path, '/');
    }

    protected static function createTempPath(): ?string
    {
        $tempDir = storage_path('app/tmp');
        if (!is_dir($tempDir)) {
            Storage::disk('local')->makeDirectory('tmp');
        }

        $path = tempnam($tempDir, 'yt_');
        return $path ?: null;
    }

    protected static function streamToTemp($stream, string $tempPath): bool
    {
        if (!$stream) {
            return false;
        }

        $target = fopen($tempPath, 'wb');
        if (!$target) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            return false;
        }

        stream_copy_to_stream($stream, $target);
        fclose($target);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return filesize($tempPath) > 0;
    }
}
