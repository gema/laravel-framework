<?php

namespace GemaDigital\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class ExceptionHandler extends Handler
{
    /**
     * Handle the incoming exceptions.
     */
    public static function handle(Exceptions $exceptions): void
    {
        $exceptions->respond(function (Response|JsonResponse $response, Throwable $exception, Request $request): Response {
            $name = basename($exception::class);
            $file = preg_replace('/\\\/', '/', str_replace(base_path(), '', $exception->getFile()));
            $message = htmlspecialchars($exception->getMessage());
            $errors = method_exists($exception, 'errors') ? $exception->errors() : ['exception' => $message];
            $code = $response->getStatusCode() ?: 400;

            return response()->api(null, -1, $code, $errors, [
                $name => [
                    'message' => $message,
                    'file' => $file,
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                ],
            ]);
        });
    }
}
