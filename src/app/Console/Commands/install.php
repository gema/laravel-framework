<?php

namespace GemaDigital\Framework\app\Console\Commands;

use Illuminate\Console\Command;

class install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up Laravel project';

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
        $app = env('APP_NAME');
        $host = env('DB_HOST');
        $port = env('DB_PORT');
        $database = env('DB_DATABASE');

        if ($this->confirm("Do you wish to install Laravel using $database database on $host:$port?")) {
            exec('npm install');
            exec('npm run prod');
            exec('php artisan key:generate');
            exec('php artisan migrate');
            exec('php artisan db:seed');
        }

        $this->info("Successfully installed $app.");
    }
}
