<?php

namespace GemaDigital\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class PackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'package';

    /**
     * The console command description.
     */
    protected $description = 'Packages the app on a zip';

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
        $app_name = preg_replace('/[^0-9a-zA-Z_]/', '', (string) preg_replace("/\s/", '-', strtolower((string) env('APP_NAME'))));
        $app_date = Carbon::now()->format('Ymdhms');

        $zipName = "$app_name-$app_date.zip";

        $this->info("Packaging $app_name to '$zipName'");

        $files = [
            'app',
            'bootstrap',
            'config',
            'public',
            'resources',
            'routes',
            'storage',
            'vendor',
            '.env',
            'server.php',
        ];

        $this->info(exec('"%ProgramFiles%\7-Zip\7z.exe" a '.$zipName.' '.implode(' ', $files)));
    }
}
