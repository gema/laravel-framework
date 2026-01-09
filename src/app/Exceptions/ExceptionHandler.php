<?php

namespace GemaDigital\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionHandler
{
    public static function handle(Exceptions $exceptions): void
    {
        $exceptions->render(self::render(...));
    }

    /**
     * Render an exception into an HTTP json response.
     */
    public static function render(Throwable $exception, Request $request): Response|false
    {
        if ($exception->getCode() === '22P02') {
            $message = str_replace('ERROR:  ', '', explode("\n", $exception->errorInfo[2])[0]);
            throw new NotFoundHttpException($message);
        }

        if (app()->hasDebugModeEnabled() && $request->expectsJson()) {
            $name = basename($exception::class);
            $file = preg_replace('/\\\/', '/', str_replace(base_path(), '', $exception->getFile()));
            $message = htmlspecialchars($exception->getMessage());
            $errors = method_exists($exception, 'errors') ? $exception->errors() : ['exception' => $message];
            $code = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : null;

            $code ??= match ($exception::class) {
                AuthenticationException::class => 401,
                Exception::class => $exception->getCode(),
                default => 400,
            };

            return response()->api(null, -1, $code, $errors, [
                $name => [
                    'message' => $message,
                    'file' => $file,
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                ],
            ]);
        }

        return false;
    }
}
