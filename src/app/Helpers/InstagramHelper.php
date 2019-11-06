<?php

namespace GemaDigital\Framework\App\Helpers;

class InstagramHelper
{
    public static function getProfile($access_token)
    {
        $data = file_get_contents("https://api.instagram.com/v1/users/self/?access_token={$access_token}");
        $json = json_decode($data, true);

        return $json;
    }

    public static function getLatestPosts($access_token, $count)
    {
        $data = file_get_contents("https://api.instagram.com/v1/users/self/media/recent/?access_token={$access_token}&count={$count}");
        $json = json_decode($data, true);

        return $json['data'];
    }
}
