<?php

namespace GemaDigital\Macros;

use GemaDigital\Helpers\QueryLogger;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Arr;

class ResponseMacros
{
    public static function register(): void
    {
        /**
         * JSON Response
         */
        ResponseFactory::macro('api', function (
            mixed $data = null,
            int $code = 0,
            int $status = 200,
            mixed $errors = null,
            mixed $exception = null
        ): Response {
            $response = [
                'code' => $code,
                'data' => $data,
                'errors' => $errors,
            ];

            $result = json_encode($response);

            if (debugMode()) {
                $time = (int) ((microtime(true) - LARAVEL_START) * 1e6);
                $timeData = $time > 1e6 ? [$time / 1e6, 's'] : (
                    $time > 1e3 ? [$time / 1e3, 'ms'] : (
                        [$time, 'μs']
                    )
                );

                $memory = memory_get_peak_usage();
                $memoryData = $memory > 1e6 ? [$memory / 1e6, 'mb'] : (
                    $memory > 1e3 ? [$memory / 1e3, 'kb'] : (
                        [$memory, 'b']
                    )
                );

                $queries = QueryLogger::list();

                $response = array_merge($response, ['debug' => [
                    'time' => [
                        'value' => $timeData[0],
                        'unit' => $timeData[1],
                    ],
                    'memory' => [
                        'value' => $memoryData[0],
                        'unit' => $memoryData[1],
                    ],
                    'exception' => $exception,
                    'query' => [
                        'count' => count($queries),
                        'time' => (float) number_format(collect($queries)->pluck('time')->sum(), 2),
                        'list' => $queries,
                    ],
                    'post' => request()->request->all(),
                ]]);

                $result = json_encode($response);
            }

            return response($result, $status)
                ->header('Content-Type', 'application/json')
                ->header('Content-Length', strval(strlen($result)));
        });

        /**
         * JSON Response with RAW data
         */
        ResponseFactory::macro('apiRaw', function (
            mixed $raw = null,
            int $code = 0,
            int $status = 200,
            mixed $errors = null,
        ): Response {
            $response = [
                'code' => $code,
                'data' => 'RAW',
                'errors' => $errors,
            ];

            $result = json_encode($response);
            $result = str_replace('"RAW"', $raw, $result);

            return response($result, $status)
                ->header('Content-Type', 'application/json')
                ->header('Content-Length', strval(strlen($result)));
        });

        /**
         * JSON Response with Error
         */
        ResponseFactory::macro('apiError', function (
            mixed $errors = null,
            int $code = -1,
            int $status = 400,
        ): Response {
            return response()->api(null, $code, $status, $errors);
        });

        /**
         * JSON Response Status
         */
        ResponseFactory::macro('apiStatus', function (
            bool $status,
            int $success = 200,
            int $fail = 400,
        ): Response {
            return response()->api(null, 0, $status ? $success : $fail);
        });

        /**
         * JSON Pagination
         */
        ResponseFactory::macro('apiPagination', function (
            mixed $data = null,
            ?LengthAwarePaginator $pagination = null,
            int $code = 0,
            int $status = 200,
            mixed $errors = null,
            mixed $exception = null,
        ): Response {
            $data = [
                ...$data,
                'pagination' => Arr::only($pagination?->toArray(), [
                    'from',
                    'to',
                    'total',
                    'per_page',
                    'last_page',
                    'current_page',
                ]),
            ];

            return response()->api($data, $code, $status, $errors, $exception);
        });
    }
}
