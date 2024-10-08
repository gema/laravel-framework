<?php

use GemaDigital\Http\Controllers\Admin\AdminActionsController;
use GemaDigital\Http\Controllers\Admin\BuildController;
use GemaDigital\Http\Controllers\Admin\CacheController;
use GemaDigital\Http\Controllers\Admin\MaintenanceController;
use GemaDigital\Http\Controllers\Admin\ViewAsController;
use GemaDigital\Http\Controllers\LangController;
use GemaDigital\Http\Controllers\SessionController;

// Framework
Route::group(['middleware' => 'web'], function () {
    // Admin
    Route::group([
        'prefix' => config('backpack.base.route_prefix', 'admin'),
        'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
        'namespace' => 'Admin',
    ], function () {
        // Admin Actions
        Route::get('terminal', [AdminActionsController::class, 'terminal'])->name('terminal');
        Route::post('terminal/run', [AdminActionsController::class, 'terminalRun'])->name('terminal_run');
        Route::get('actions', [AdminActionsController::class, 'actions'])->name('actions');

        // Build
        Route::post('build', [BuildController::class, 'build'])->name('build');

        // View as
        Route::any('view-as-role/{role}', [ViewAsController::class, 'viewAsRole'])->name('view-as-role');
        Route::any('view-as-permission/{permission}/{state}', [ViewAsController::class, 'viewAsPermission'])->name('view-as-permission');

        // Cache
        Route::group(['prefix' => 'cache'], function () {
            Route::post('flush', [CacheController::class, 'flush']);
            Route::post('config', [CacheController::class, 'config']);
            Route::post('config/clear', [CacheController::class, 'configClear']);
            Route::post('route', [CacheController::class, 'route']);
            Route::post('route/clear', [CacheController::class, 'routeClear']);
            Route::post('view', [CacheController::class, 'view']);
            Route::post('view/clear', [CacheController::class, 'viewClear']);
        });

        // Maintenance
        Route::group(['prefix' => 'maintenance'], function () {
            Route::post('up', [MaintenanceController::class, 'up']);
            Route::post('down', [MaintenanceController::class, 'down']);
        });
    });

    // Session
    Route::any('session/flush', [SessionController::class, 'flush']);

    // Language
    Route::any('lang/{locale}', [LangController::class, 'setLang'])
        ->where('locale', '[a-z]{2}(-[A-Z]{2})?')->name('lang');
});

// Admin Overrides
Route::group(
    [
        'prefix' => config('backpack.base.route_prefix', 'admin'),
        'namespace' => '\App\Http\Controllers\Admin',
        'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    ], function () {
        // User
        Route::crud('user', 'UserCrudController');
    });
