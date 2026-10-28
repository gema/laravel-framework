<?php

namespace GemaDigital\Notifications;

use GemaDigital\Auth\MagicLink;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MagicLinkNotification extends Notification
{
    public function __construct(public string $code, public string $url) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification: the code (also in the subject, to show in
     * the phone's notification) and a button to log in with one click.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $app = config('app.name');
        $code = substr($this->code, 0, 3).' '.substr($this->code, 3);

        return (new MailMessage)
            ->subject($this->text('subject', ['code' => $code, 'app' => $app]))
            ->line($this->text('intro', ['app' => $app]))
            ->line('**'.$code.'**')
            ->line($this->text('or'))
            ->action($this->text('action'), $this->url)
            ->line($this->text('expires', ['minutes' => MagicLink::MINUTES]))
            ->line($this->text('ignore'));
    }

    /**
     * A line of the email, in the app's language.
     *
     * @param  array<string, mixed>  $replace
     */
    protected function text(string $key, array $replace = []): string
    {
        $text = trans('gemadigital::magic_link.mail.'.$key, $replace);

        return is_string($text) ? $text : $key;
    }
}
