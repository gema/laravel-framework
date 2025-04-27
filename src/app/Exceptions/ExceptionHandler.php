<?php

namespace GemaDigital\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class ExceptionHandler
{
    public static function handle(Exceptions $exceptions): void
    {
        $exceptions->render(self::render(...));
    }

    public static function render(Throwable $exception, Request $request)
    {
        if ($request->expectsJson()) {
            $name = basename($exception::class);
            $file = preg_replace('/\\\/', '/', str_replace(base_path(), '', $exception->getFile()));
            $message = htmlspecialchars($exception->getMessage());
            $errors = method_exists($exception, 'errors') ? $exception->errors() : ['exception' => $message];

            // error code
            $code = match (true) {
                $exception instanceof AuthorizationException => 403,
                $exception instanceof AuthenticationException => 401,
                default => array_key_exists($exception->getCode(), Response::$statusTexts) ? $exception->getCode() : 400,
            };

            return json_response(null, -1, $code, $errors, [
                $name => [
                    'message' => $message,
                    'file' => $file,
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                ],
            ]);
        }
    }
}
