<?php
namespace Modules\AppChannelThreadsUnofficial\Facades;

use Illuminate\Support\Facades\Facade;
use Media;

class Post extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ex_str(__NAMESPACE__);
    }

    public static function validator($post)
    {
        $errors = [];
        $data = json_decode($post->data, true);
        $medias = $data['medias'] ?? [];

        if (!in_array($post->type, ['text', 'link', 'media'])) {
            $errors[] = __('Threads only supports post types: text, link, or media.');
        }

        if ($post->type === 'media') {
            if (empty($medias)) {
                $errors[] = __('Media is required for Threads media posts.');
            } else {
                foreach ($medias as $media) {
                    if (!Media::isImg($media) && !Media::isVideo($media)) {
                        $errors[] = __('Threads media posts only support images or videos.');
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    public static function post($post)
    {
        $data = json_decode($post->data, false);

        $caption = spintax($data->caption ?? '');
        $medias = $data->medias ?? [];
        $link = $data->link ?? null;

        return \ThreadsUnofficial::createPost([
            'account' => $post->account,
            'type' => $post->type,
            'caption' => $caption,
            'medias' => $medias,
            'link' => $link,
            'access_token' => $post->account->token,
            'user_id' => $post->account->pid ?? null,
            'username' => $post->account->username ?? null,
        ]);
    }
}
