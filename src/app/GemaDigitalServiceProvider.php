<?php

namespace GemaDigital;

use GemaDigital\Console\Commands\DuplicateCommand;
use GemaDigital\Console\Commands\ExportJsonsCommand;
use GemaDigital\Console\Commands\MagicLinkCommand;
use GemaDigital\Console\Commands\PackageCommand;
use GemaDigital\Console\Commands\PublishCommand;
use GemaDigital\Helpers\QueryLogger;
use GemaDigital\Mail\MicrosoftGraphTransport;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\Factory;

class GemaDigitalServiceProvider extends ServiceProvider
{
    /**
     * The console commands to be registered.
     *
     * @var array<class-string>
     */
    protected array $commands = [
        DuplicateCommand::class,
        PackageCommand::class,
        PublishCommand::class,
        ExportJsonsCommand::class,
        MagicLinkCommand::class,
    ];

    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'gemadigital');
        // Laravel's own strings, such as the emails' greeting and footer; the app's translations come first
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'gemadigital');
        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        // Log all queries
        if (config('app.debug')) {
            DB::listen(fn (QueryExecuted $log) => QueryLogger::log($log));
        }

        // Mail through Microsoft Graph: MAIL_MAILER=microsoft-graph
        $this->callAfterResolving('mail.manager', function (MailManager $manager): void {
            $manager->extend('microsoft-graph', fn (array $config) => MicrosoftGraphTransport::fromConfig($config));
        });

        $this->registerSocialiteDrivers();

        // Blade directives

        // Is (Role, Permission)
        Blade::directive('is', fn ($roles, $permissions = null) => "<?php if (is($roles, $permissions)) { ?>");

        Blade::directive('elseis', fn () => '<?php } else { ?>');

        Blade::directive('endis', fn () => '<?php } ?>');
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        // register all configs
        $this->loadConfigs();

        // register the helper functions
        $this->loadHelpers();

        if (config('gemadigital.magic_link_login')) {
            $this->registerMagicLinkLogin();
        }
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
            __DIR__.'/../config/gemadigital.php' => config_path('gemadigital.php'),
        ]);

        // Publishing the views.
        $this->publishes([
            __DIR__.'/../resources/views/vendor/backpack/crud' => base_path('resources/views/vendor/backpack/crud'),
        ], 'framework.views');

        // Publishing the translation files.
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/gemadigital'),
        ], 'framework.views');

        // Registering package commands.
        $this->commands($this->commands);
    }

    /**
     * Load helper methods, for convenience.
     */
    public function loadHelpers(): void
    {
        require_once __DIR__.'/Helpers/GlobalHelpers.php';
        require_once __DIR__.'/Helpers/GeneralHelpers.php';
    }

    /**
     * Socialite drivers from installed socialiteproviders packages, so apps don't need their own listener.
     * Registering again (e.g. in the app's EventServiceProvider) is harmless.
     */
    protected function registerSocialiteDrivers(): void
    {
        // Microsoft login (azure): socialiteproviders/microsoft-azure
        if (class_exists('SocialiteProviders\Azure\AzureExtendSocialite')) {
            Event::listen('SocialiteProviders\Manager\SocialiteWasCalled', 'SocialiteProviders\Azure\AzureExtendSocialite@handle');
        }
    }

    /**
     * Login by email code instead of password (MAGIC_LINK_LOGIN): Backpack's password login, registration form
     * and password reset go away, and the theme's login form and My Account page become the framework's.
     */
    protected function registerMagicLinkLogin(): void
    {
        // The framework's routes take their place (routes.php). Backpack's registration_open only shows links to
        // its register form; registering by email follows gemadigital.registration_open
        config([
            'backpack.base.setup_auth_routes' => false,
            'backpack.base.registration_open' => false,
        ]);

        // Ahead of the app's own copies of those views too
        $this->callAfterResolving('view', function (Factory $view): void {
            $view->prependNamespace(Str::before((string) config('backpack.ui.view_namespace'), '::'), __DIR__.'/../resources/views/magic_link/theme');
        });
    }

    /**
     * Load configs methods, for convenience.
     */
    public function loadConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/gemadigital.php', 'gemadigital');

        // Extra mailers (microsoft-graph)
        $this->replaceConfigRecursivelyFrom(__DIR__.'/../config/mail.php', 'mail');
    }
}
