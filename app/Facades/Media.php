<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Storage;

class Media extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    { 
        return 'media';
    }

    /**
     * Return an array of available storage disks with labels.
     */
    protected static function disks()
    { 
        return [
            'aws'      => __("AmazonS3"),
            'contabo'  => __("Contabo S3"),
            'local'    => __("Public"),
        ];
    }

    /**
     * Get the URL for a given file path.
     * - If $path is empty, returns an empty string.
     * - If $path is already a valid URL, returns it directly.
     * - If the default storage disk is S3 (aws or s3), returns the S3 URL.
     * - Otherwise, assumes local/public storage.
     */
    protected static function url($path = '')
    {
        if (empty($path)) {
            return url('storage/');
        }
        
        // If already a valid URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, 'storage/') || str_starts_with($path, '/storage/')) {
            return url(ltrim($path, '/'));
        }
        
        $disk = config('filesystems.default');
        if (in_array($disk, ['aws', 's3'])) {
            return Storage::disk($disk)->url($path);
        }
        
        return url('storage/' . ltrim($path, '/'));
    }

    /**
     * Generate (or reuse) a thumbnail image for a local video file using FFmpeg.
     */
    protected static function videoThumbnail($path, $second = 1)
    {
        if (empty($path)) {
            return '';
        }

        $sourceUrl = self::url($path);
        $relativePath = self::getPathFromUrl($sourceUrl);

        if (empty($relativePath)) {
            return '';
        }

        $storageDisk = Storage::disk('public');
        if (!$storageDisk->exists($relativePath)) {
            return '';
        }

        $thumbnailDir = 'thumbnails/videos';
        $thumbnailName = md5($relativePath) . '.jpg';
        $thumbnailPath = $thumbnailDir . '/' . $thumbnailName;

        if ($storageDisk->exists($thumbnailPath)) {
            return self::url($thumbnailPath);
        }

        $storageDisk->makeDirectory($thumbnailDir);

        $inputPath = $storageDisk->path($relativePath);
        $outputPath = $storageDisk->path($thumbnailPath);

        $second = max(0, (int) $second);
        $timeStamp = sprintf('00:00:%02d', $second);
        $command = sprintf(
            'ffmpeg -y -ss %s -i %s -frames:v 1 -q:v 2 %s 2>/dev/null',
            escapeshellarg($timeStamp),
            escapeshellarg($inputPath),
            escapeshellarg($outputPath)
        );

        exec($command, $output, $status);

        if ($status === 0 && $storageDisk->exists($thumbnailPath)) {
            return self::url($thumbnailPath);
        }

        return '';
    }

    /**
     * Extract the relative file path from a full URL.
     */
    protected static function getPathFromUrl($path = '')
    { 
        $baseUrl = url('storage/');
        return str_replace($baseUrl . "/", "", $path);
    }

    /**
     * Get the full filesystem path for the given file.
     */
    protected static function path($path, $storageType = "public")
    { 
        return Storage::disk($storageType)->path($path);
    }

    /**
     * Determine if the given path or URL points to an image.
     * For local files, uses getimagesize; for URLs, delegates to isImgUrl().
     */
    protected static function isImg($path)
    {
        // 1. Nếu là local file thực sự (file_exists)
        if (file_exists($path)) {
            $imgInfo = @getimagesize($path);
            if ($imgInfo && isset($imgInfo[2])) {
                // Check against common image type constants
                return in_array($imgInfo[2], [
                    IMAGETYPE_GIF,
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_WBMP,
                    IMAGETYPE_WEBP
                ]);
            }
            return false;
        }

        // 2. Nếu là URL hoặc cần convert sang URL
        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            $path = self::url($path);
        }

        // 3. Nếu là URL
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return self::isImgUrl($path);
        }

        // 4. Nếu là file local sau khi chuyển qua url() mà vẫn là file local
        if (file_exists($path)) {
            $imgInfo = @getimagesize($path);
            if ($imgInfo && isset($imgInfo[2])) {
                return in_array($imgInfo[2], [
                    IMAGETYPE_GIF,
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_WBMP,
                    IMAGETYPE_WEBP
                ]);
            }
        }

        return false;
    }
    
    /**
     * Check if the given URL points to a valid image.
     */
    protected static function isImgUrl($url)
    {   
        // Suppress warnings in case of connection issues.
        $headers = @get_headers($url, 1);
        if (!$headers) {
            return false;
        }

        // Follow redirect if necessary.
        if (isset($headers['location'])) {
            $redirectUrl = is_array($headers['location']) ? $headers['location'][0] : $headers['location'];
            $headers = @get_headers($redirectUrl, 1) ?: $headers;
        }

        $headers = array_change_key_case($headers, CASE_LOWER);
        $contentType = '';
        if (isset($headers['content-type'])) {
            $contentType = is_array($headers['content-type'])
                ? strtolower($headers['content-type'][0])
                : strtolower($headers['content-type']);
        }

        $imgTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($contentType, $imgTypes)) {
            return true;
        }
        
        // Fallback: check file extension
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
            return true;
        }
        
        return false;
    }

    protected static function isDocument($path)
    {
        // Nếu $path không phải URL, chuyển đổi sử dụng url().
        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            $path = self::url($path);
        }

        $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION));
        $docExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'];

        return in_array($ext, $docExtensions);
    }

    protected static function isOgg($path)
    {
        // Nếu $path không phải URL, chuyển đổi sử dụng url().
        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            $path = self::url($path);
        }

        $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION));

        return $ext === 'ogg';
    }

    protected static function isAudio($path)
    {
        // Nếu $path không phải URL, chuyển đổi sử dụng url().
        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            $path = self::url($path);
        }

        $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION));
        $audioExtensions = ['mp3', 'ogg', 'wav', 'flac', 'aac'];

        return in_array($ext, $audioExtensions);
    }

    /**
     * Check if the given path or URL points to a video.
     */
    protected static function isVideo($path)
    {
        // 1. Nếu là file local thực sự (file_exists)
        if (file_exists($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            // Bạn có thể mở rộng thêm các định dạng video khác nếu muốn
            return in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm']);
        }

        // 2. Nếu không phải URL, thử chuyển thành URL
        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            $path = self::url($path);
        }

        // 3. Nếu là URL, kiểm tra content-type header
        try {
            $streamOpts = [
                "ssl" => [
                    "verify_peer"      => false,
                    "verify_peer_name" => false,
                ]
            ];

            $headers = @get_headers($path, 1, stream_context_create($streamOpts));
            if (!$headers) {
                return false;
            }

            $headers = array_change_key_case($headers, CASE_LOWER);
            $contentType = '';
            if (isset($headers['content-type'])) {
                $contentType = is_array($headers['content-type'])
                    ? strtolower($headers['content-type'][0])
                    : strtolower($headers['content-type']);
            }

            // Nếu content-type là video/*
            if (strpos($contentType, 'video/') === 0) {
                return true;
            }

            // Fallback: check đuôi file
            $ext = strtolower(pathinfo(strtok($path, '?'), PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm'])) {
                return true;
            }

        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Detect the file type based on its extension.
     */
    protected static function detectFileType($ext)
    {
        $ext = strtolower($ext);
        $mapping = [
            'jpg'   => 'image',
            'jpeg'  => 'image',
            'png'   => 'image',
            'gif'   => 'image',
            'svg'   => 'image',
            'webp'  => 'image',
            'mp4'   => 'video',
            'mov'   => 'video',
            'csv'   => 'csv',
            'pdf'   => 'pdf',
            'xlsx'  => 'doc',
            'xls'   => 'doc',
            'docx'  => 'doc',
            'doc'   => 'doc',
            'txt'   => 'doc',
            'mp3'   => 'audio',
            'ogg'   => 'audio',
        ];

        return $mapping[$ext] ?? 'other';
    }

    /**
     * Get file icon and color based on the detected file type.
     */
    protected static function detectFileIcon($type)
    {
        switch ($type) {
            case 'image':
                return ["color" => "gray",    "icon" => "fa-light fa-image"];
            case 'video':
                return ["color" => "white", "icon" => "fa-light fa-film"];
            case 'audio':
                return ["color" => "primary", "icon" => "fa-light fa-volume"];
            case 'csv':
                return ["color" => "info",    "icon" => "fa-light fa-file-csv"];
            case 'pdf':
                return ["color" => "cyan",    "icon" => "fa-light fa-file-pdf"];
            case 'doc':
                return ["color" => "success", "icon" => "fa-light fa-file-contract"];
            case 'zip':
                return ["color" => "primary", "icon" => "fa-light fa-file-zipper"];
            default:
                return ["color" => "primary", "icon" => "fa-light fa-file-circle-question"];
        }
    }
}
