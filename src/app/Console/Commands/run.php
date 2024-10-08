<?php

namespace GemaDigital\Console\Commands;

use Illuminate\Console\Command;

class run extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'run';

    /**
     * The console command description.
     */
    protected $description = 'artisan serve + npm run watch';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $app_name = env('APP_NAME');
        $url = parse_url((string) env('APP_URL'));
        $db_connection = env('DB_CONNECTION');
        $db_name = env('DB_DATABASE');
        $db_host = env('DB_HOST');
        $db_port = env('DB_PORT');
        $env = env('APP_ENV');
        $debug = env('APP_DEBUG');
        $dir = getcwd();

        $port = $url['port'];
        $host = $url['host'];

        $this->info("Running $app_name on $host:$port");
        $this->info("$db_connection on $db_host:$db_port connected to $db_name");
        $this->info("$env environment");
        $this->info(($debug ? 'debug' : 'production').' mode');

        // Git Garbage Collect
        pclose(popen('start /B powershell -Command -WindowStyle Minimized "git gc"', 'r'));

        // NPM Watch
        $commands = [
            // Artisan Serve
            'wt --title "Artisan" -d "'.$dir.'" cmd /k "php artisan serve --port '.$port.' --host '.$host.'"',

            // NPM run watch
            'new-tab --title "Laravel Mix" -d "'.$dir.'" cmd /k "npm run watch"',

            // Empty tab
            'new-tab --title "'.$app_name.'" -d "'.$dir.'"',
        ];

        pclose(popen(implode(' ; ', $commands), 'r'));
    }
}
