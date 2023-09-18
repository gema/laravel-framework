<?php

namespace GemaDigital\Framework\app\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use ZanySoft\Zip\Facades\Zip;

class package extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Packages the app on a zip';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $app_name = preg_replace('/[^0-9a-zA-Z_]/', '', preg_replace("/\s/", '-', strtolower(env('APP_NAME'))));
        $app_date = Carbon::now()->format('Ymdhms');

        $zipName = "$app_name-$app_date.zip";
        $zipPath = base_path().'\\'.$zipName;

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

        if ($this->confirm('Try to use 7zip instead of PHP Zip?', 'yes')) {
            $this->info(exec('"%ProgramFiles%\7-Zip\7z.exe" a '.$zipName.' '.implode(' ', $files)));
        } else {
            $zip = Zip::create($zipPath);
            $zip->add($files);
            $zip->close();

            $this->info(Zip::check($zipPath) ? 'Success' : 'Error');
        }
    }
}
