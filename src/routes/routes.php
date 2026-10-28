<?php

use GemaDigital\Http\Controllers\Admin\AdminActionsController;
use GemaDigital\Http\Controllers\Admin\BuildController;
use GemaDigital\Http\Controllers\Admin\CacheController;
use GemaDigital\Http\Controllers\Admin\ImpersonateController;
use GemaDigital\Http\Controllers\Admin\MaintenanceController;
use GemaDigital\Http\Controllers\LangController;
use GemaDigital\Http\Controllers\MagicLinkController;
use GemaDigital\Http\Controllers\SessionController;
use GemaDigital\Http\Controllers\SocialiteController;
use GemaDigital\Http\Controllers\VitalsController;
use GemaDigital\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

// Admin (AdminOnly: the project's AdminPanelAccess may let other users into the panel)
Route::group([
    'prefix' => config('gemadigital.routes.prefix', 'admin'),
    'middleware' => ['web', config('gemadigital.routes.middleware', 'admin'), AdminOnly::class],
], function () {
    // Admin Actions
    Route::get('actions', [AdminActionsController::class, 'actions'])->name('actions');

    // Build
    if (config('gemadigital.build.enabled')) {
        Route::post('build', [BuildController::class, 'build'])->name('build');
    }

    // Impersonate
    Route::get('impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave')->withoutMiddleware([config('gemadigital.routes.middleware', 'admin'), AdminOnly::class]);
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

// Login by email code or link (MAGIC_LINK_LOGIN), in place of Backpack's auth routes
if (config('gemadigital.magic_link_login')) {
    Route::group([
        'prefix' => config('backpack.base.route_prefix', 'admin'),
        'middleware' => config('backpack.base.web_middleware', 'web'),
    ], function () {
        Route::middleware('guest:'.config('backpack.base.guard'))->group(function () {
            Route::get('login', [MagicLinkController::class, 'showLoginForm'])->name('backpack.auth.login');
            Route::post('login', [MagicLinkController::class, 'send']);
            Route::post('login/code', [MagicLinkController::class, 'code'])->name('magic-link.code');
            Route::post('login/name', [MagicLinkController::class, 'register'])->name('magic-link.register');
            Route::get('login/restart', [MagicLinkController::class, 'restart'])->name('magic-link.restart');
            Route::get('login/link/{token}', [MagicLinkController::class, 'show'])->name('magic-link');
            Route::post('login/link/{token}', [MagicLinkController::class, 'link']);
        });

        Route::get('logout', 'Backpack\CRUD\app\Http\Controllers\Auth\LoginController@logout')->name('backpack.auth.logout');
        Route::post('logout', 'Backpack\CRUD\app\Http\Controllers\Auth\LoginController@logout');
    });
}

// Public
Route::group(['middleware' => 'web'], function () {
    // Session
    Route::any('session/flush', [SessionController::class, 'flush']);

    // Language
    Route::any('lang/{locale}', [LangController::class, 'setLang'])
        ->where('locale', '[a-z]{2}(-[A-Z]{2})?')
        ->name('lang');

    // Socialite
    Route::get('/auth/redirect/{driver}', [SocialiteController::class, 'login'])
        ->where('driver', 'google|azure')
        ->name('socialite.login');

    Route::get('/auth/callback/{driver}', [SocialiteController::class, 'callback'])
        ->where('driver', 'google|azure')
        ->name('socialite.callback');

    // Vitals
    Route::get('/vitals', VitalsController::class)
        ->middleware('throttle:10,1')
        ->name('vitals');

    // Pages
    // Route::get('{page}/{subs?}', [PageController::class, 'index'])
    //     ->where(['page' => '^((?!admin).)|[^/]*$', 'subs' => '.*']);
});
