<?php

use GemaDigital\Http\Controllers\Admin\AdminActionsController;
use GemaDigital\Http\Controllers\Admin\BuildController;
use GemaDigital\Http\Controllers\Admin\CacheController;
use GemaDigital\Http\Controllers\Admin\ImpersonateController;
use GemaDigital\Http\Controllers\Admin\MaintenanceController;
use GemaDigital\Http\Controllers\LangController;
use GemaDigital\Http\Controllers\SessionController;

// Admin
Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
], function () {
    // Admin Actions
    Route::get('terminal', [AdminActionsController::class, 'terminal'])->name('terminal');
    Route::post('terminal/run', [AdminActionsController::class, 'terminalRun'])->name('terminal_run');
    Route::get('actions', [AdminActionsController::class, 'actions'])->name('actions');

    // Build
    Route::post('build', [BuildController::class, 'build'])->name('build');

    // Impersonate
    Route::get('impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave')->withoutMiddleware(config('backpack.base.middleware_key', 'admin'));
    Route::get('impersonate/{id}', [ImpersonateController::class, 'impersonate'])->name('impersonate');

    // Cache
    Route::post('cache/flush', [CacheController::class, 'flush']);
    Route::post('cache/config', [CacheController::class, 'config']);
    Route::post('cache/config/clear', [CacheController::class, 'configClear']);
    Route::post('cache/route', [CacheController::class, 'route']);
    Route::post('cache/route/clear', [CacheController::class, 'routeClear']);
    Route::post('cache/view', [CacheController::class, 'view']);
    Route::post('cache/view/clear', [CacheController::class, 'viewClear']);

    // Maintenance
    Route::post('maintenance/up', [MaintenanceController::class, 'up']);
    Route::post('maintenance/down', [MaintenanceController::class, 'down']);
});

// Public
Route::group(['middleware' => 'web'], function () {
    // Session
    Route::any('session/flush', [SessionController::class, 'flush']);

    // Language
    Route::any('lang/{locale}', [LangController::class, 'setLang'])
        ->where('locale', '[a-z]{2}(-[A-Z]{2})?')->name('lang');

    // Pages
    // Route::get('{page}/{subs?}', [PageController::class, 'index'])
    //     ->where(['page' => '^((?!admin).)|[^/]*$', 'subs' => '.*']);
});
