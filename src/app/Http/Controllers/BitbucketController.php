<?php

namespace GemaDigital\Framework\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BitbucketController extends Controller
{
    public function webhook(Request $request)
    {
        $event = $request->header('X-Event-Key');
        $payload = json_decode($request->getContent(), true);

        $class = 'App\Http\Controllers\BitbucketController';
        if (class_exists($class)) {
            $instance = new $class();
            echo $instance->webhook($event, $payload);
        }
    }
}
