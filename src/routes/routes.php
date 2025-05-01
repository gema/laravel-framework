<?php

use App\Models\User;
use GemaDigital\Http\Controllers\Admin\AdminActionsController;
use GemaDigital\Http\Controllers\Admin\BuildController;
use GemaDigital\Http\Controllers\Admin\CacheController;
use GemaDigital\Http\Controllers\Admin\ImpersonateController;
use GemaDigital\Http\Controllers\Admin\MaintenanceController;
use GemaDigital\Http\Controllers\LangController;
use GemaDigital\Http\Controllers\SessionController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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

// Socialite login
Route::get('/auth/redirect/{driver}', fn (string $driver): RedirectResponse => Socialite::driver($driver)->redirect())
    ->name('socialite.login');

Route::get('/auth/callback/{driver}', function (string $driver): RedirectResponse {
    $socialUser = Socialite::driver($driver)->user();

    $user = User::query()
        ->where('email', $socialUser->getEmail())
        ->firstOr(fn () => User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
            'password' => bcrypt(Str::random(16)),
        ]));

    $user->name = $socialUser->getName();
    $user->avatar = $socialUser->getAvatar();
    $user->socialite = [
        ...(array) $user->socialite,
        $driver => [
            'id' => $socialUser->getId(),
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
        ],
    ];
    $user->save();

    // Check user domain
    if (in_array(Str::afterLast($user->email, '@'), config('gemadigital.auto_admin_domains', []))) {
        $user->assignRole('admin');
    }

    Auth::login($user);

    return redirect(route('backpack.dashboard'));
});
