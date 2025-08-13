<?php

namespace GemaDigital\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Throwable;

class ExceptionHandler
{
    public static function handle(Exceptions $exceptions): void
    {
        $exceptions->render(self::render(...));
    }

    /** @phpstan-ignore-next-line */
    public static function render(Throwable $exception, Request $request)
    {
        if ($request->expectsJson()) {
            $name = basename($exception::class);
            $file = preg_replace('/\\\/', '/', str_replace(base_path(), '', $exception->getFile()));
            $message = htmlspecialchars($exception->getMessage());
            $errors = method_exists($exception, 'errors') ? $exception->errors() : ['exception' => $message];
            $code = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400;

            return response()->api(null, -1, $code, $errors, [
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
