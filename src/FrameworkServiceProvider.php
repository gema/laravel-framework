<?php

namespace GemaDigital\Framework;

use Illuminate\Support\ServiceProvider;

class FrameworkServiceProvider extends ServiceProvider
{
    protected $commands = [
        \GemaDigital\Framework\app\Console\Commands\duplicate::class,
        \GemaDigital\Framework\app\Console\Commands\install::class,
        \GemaDigital\Framework\app\Console\Commands\package::class,
        \GemaDigital\Framework\app\Console\Commands\publish::class,
        \GemaDigital\Framework\app\Console\Commands\run::class,
    ];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'gemadigital');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'gemadigital');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        // Blade directives

        // SVG
        \Blade::directive('svg', function ($arguments) {
            list($path, $class, $style) = array_pad(explode(',', trim($arguments.',,', '() ')), 2, '');
            $path = trim($path, "' ");
            $class = trim($class, "' ");
            $style = trim($style, "' ");

            $svg = new \DOMDocument();
            $svg->load(public_path($path));
            $svg->documentElement->setAttribute('class', $class);
            $svg->documentElement->setAttribute('style', $style);

            return $svg->saveXML($svg->documentElement);
        });

        // Is (Role, Permission)
        \Blade::directive('is', function ($roles, $permissions = null) {
            return "<?php if (is($roles, $permissions)) { ?>";
        });

        \Blade::directive('elseis', function () {
            return '<?php } else { ?>';
        });

        \Blade::directive('endis', function () {
            return '<?php } ?>';
        });

        // Has (permissions)
        \Blade::directive('hasAll', function ($permissions) {
            return "<?php if (hasAllPermissions($permissions)) { ?>";
        });

        \Blade::directive('hasAny', function ($permissions) {
            return "<?php if (hasAnyPermissions($permissions)) { ?>";
        });

        \Blade::directive('has', function ($permission) {
            return "<?php if (hasPermission($permission)) { ?>";
        });

        \Blade::directive('elsehas', function () {
            return '<?php } else { ?>';
        });

        \Blade::directive('endhas', function () {
            return '<?php } ?>';
        });

        \Blade::component('gemadigital::components.responsive-picture', 'responsive-picture');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Register the service the package provides.
        $this->app->singleton('framework', function ($app) {
            return new Framework();
        });

        // register all configs
        $this->loadConfigs();

        // register the helper functions
        $this->loadHelpers();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['framework'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/config/framework.php' => config_path('gemadigital.php'),
            __DIR__.'/config/enums.php' => config_path('enums.php'),
        ]);

        // Publishing the views.
        $this->publishes([
            __DIR__.'/resources/views/vendor/backpack/crud' => base_path('resources/views/vendor/backpack/crud'),
        ], 'framework.views');

        // Publishing the translation files.
        $this->publishes([
            __DIR__.'/resources/lang' => resource_path('lang/vendor/gemadigital'),
        ], 'framework.views');

        // Registering package commands.
        $this->commands($this->commands);
    }

    /**
     * Load helper methods, for convenience.
     */
    public function loadHelpers()
    {
        require_once __DIR__.'/app/Helpers/CommonHelper.php';
    }

    /**
     * Load configs methods, for convenience.
     */
    public function loadConfigs()
    {
        // Framework config
        $this->mergeConfigFrom(__DIR__.'/config/framework.php', 'gemadigital');

        // Services config
        $this->mergeConfigFrom(__DIR__.'/config/services.php', 'services');

        // Enums config
        $this->mergeConfigFrom(__DIR__.'/config/enums.php', 'enums');

        // File Systems config
        $this->mergeConfigFrom(__DIR__.'/config/filesystems.php', 'filesystems');
    }
}
