<?php

namespace GemaDigital\Console\Commands;

use Illuminate\Console\Command;

class publish extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'boilerplate:publish';

    /**
     * The console command description.
     */
    protected $description = 'Publishes the gemadigital assets';

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
     */
    public function handle(): void
    {
        if ($this->confirm('Do you wish to publish gemadigital assets?')) {
            $force = $this->confirm('Do you want to force the replace of older assets?');

            exec('php artisan vendor:publish --provider="GemaDigital\GemaDigitalServiceProvider"'.($force ? ' --force' : ''));
        }
    }
}
