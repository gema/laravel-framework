<?php

namespace GemaDigital\Framework\app\Http\Controllers\API;

use Illuminate\Routing\Controller;

class APIController extends Controller
{
    // CURL Helper
    public function curl_request($url, $post = null)
    {
        $curl = curl_init();

        // Options for GET or POST
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36',
        ];

        // POST only options
        if ($post) {
            $options = array_merge($options, [
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($post),
                CURLOPT_POST => 1,
                CURLOPT_PORT => 3000,
            ]);
        }

        curl_setopt_array($curl, $options);

        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }

    // Safe decode json
    public function json_decode_safe($data)
    {
        $json = json_decode($data);
        return json_last_error() === 0 ? $json : $data;
    }
}
