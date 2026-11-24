<?php

namespace GemaDigital\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:test
                            {to : The address to send the test mail to}
                            {--mailer= : A mailer of the mail config other than the default, such as microsoft-graph}';

    /**
     * The console command description.
     */
    protected $description = 'Sends a test mail, to check the mail settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var string $to */
        $to = $this->argument('to');

        /** @var string $name */
        $name = $this->option('mailer') ?: config('mail.default');

        $subject = 'Test mail from '.config('app.name');

        try {
            /** @var Mailer $mailer */
            $mailer = Mail::mailer($name);

            // The transport says where it goes, such as smtp://host:port, without the credentials
            $this->line("Sending to $to with the $name mailer (".$mailer->getSymfonyTransport().')...');

            // The URL tells apart the servers of the same app
            $mailer->raw("$subject (".config('app.url')."), sent with the $name mailer.", fn (Message $message) => $message->to($to)->subject($subject));
        } catch (Exception $e) {
            $this->error('Not sent: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Sent.');

        return self::SUCCESS;
    }
}
