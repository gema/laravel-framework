<?php

namespace GemaDigital\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class VitalsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $data = Cache::remember('app:vitals', 300, fn (): array => [
            'status' => true,
            'app' => config('app.name'),
            'laravel' => (int) explode('.', app()->version())[0],
            'php' => (float) (PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION),
            'database' => $this->checkDb(),
            'storage' => $this->checkStorage(),
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json($data);
    }

    private function checkDb(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function checkStorage(): float
    {
        $path = storage_path('app');
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        return $total ? round((($total - $free) / $total), 2) : 0;
    }
}
