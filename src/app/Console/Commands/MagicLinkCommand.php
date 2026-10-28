<?php

namespace GemaDigital\Console\Commands;

use App\Models\User;
use GemaDigital\Auth\MagicLink;
use Illuminate\Console\Command;

class MagicLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'auth:magic-link {email}';

    /**
     * The console command description.
     */
    protected $description = 'Prints a login link for the email, for when the mail is down';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var string $email */
        $email = $this->argument('email');

        if (! MagicLink::active()) {
            $this->error('The login by email is off (MAGIC_LINK_LOGIN), or the route cache is from before it.');

            return self::FAILURE;
        }

        if (! config('gemadigital.registration_open') && ! User::query()->where('email', $email)->exists()) {
            $this->error('There is no user with this email and registration is closed.');

            return self::FAILURE;
        }

        // Only the link: the code works in the browser that asked for it, and asking again would send a new one
        $this->line(MagicLink::create($email)['url']);
        $this->comment('Single use, expires in '.MagicLink::MINUTES.' minutes.');

        return self::SUCCESS;
    }
}
