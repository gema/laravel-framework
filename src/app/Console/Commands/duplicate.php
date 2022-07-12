<?php

namespace GemaDigital\Framework\app\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class duplicate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:duplicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'duplicates a new project on a zip';

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
        $appName = preg_replace('/[^0-9a-zA-Z_-]/', '', preg_replace("/\s/", '-', strtolower(env('APP_NAME'))));
        $appDate = Carbon::now()->format('YmdHis');

        $zipName = "$appName-$appDate.zip";
        $zipPath = base_path($zipName);

        $this->info("Packaging $appName to '$zipName'");

        $exclude = [
            '.git',
            'node_modules',
            'vendor',
            'public/gemadigital',
            'public/packages',
            'resources/ffmpeg',
            'storage/logs',
            'storage/debugbar',
            'auth.json',
            'sftp-config.json',
            '*.zip',
        ];

        $this->info(exec('"%ProgramFiles%\7-Zip\7z.exe" a '.$zipName.' -spf -xr0!'.implode(' -xr0!', $exclude)));
    }
}
