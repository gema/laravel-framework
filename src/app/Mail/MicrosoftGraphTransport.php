<?php

namespace GemaDigital\Mail;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Part\DataPart;

/**
 * Sends mail through Microsoft Graph (POST /users/{sender}/sendMail) instead of SMTP.
 * Needs an Entra ID app with the Mail.Send application permission (client credentials).
 */
class MicrosoftGraphTransport extends AbstractTransport
{
    public function __construct(
        protected string $tenantId,
        protected string $clientId,
        protected string $clientSecret,
        protected ?string $sender = null,
        protected bool $saveToSentItems = true,
    ) {
        parent::__construct();
    }

    /**
     * Build the transport from a mailer in config/mail.php.
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(array $config): self
    {
        foreach (['tenant_id', 'client_id', 'client_secret'] as $key) {
            if (! is_string($config[$key] ?? null) || $config[$key] === '') {
                throw new InvalidArgumentException("The microsoft-graph mailer needs the [{$key}] option.");
            }
        }

        return new self(
            $config['tenant_id'],
            $config['client_id'],
            $config['client_secret'],
            is_string($config['sender'] ?? null) && $config['sender'] !== '' ? $config['sender'] : null,
            filter_var($config['save_to_sent_items'] ?? true, FILTER_VALIDATE_BOOL),
        );
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();

        if (! $original instanceof Message) {
            throw new TransportException('Microsoft Graph only sends messages with headers and parts, not raw MIME.');
        }

        $email = MessageConverter::toEmail($original);
        $envelope = $message->getEnvelope();

        // The mailbox that sends: the configured one, or the message's sender
        $sender = $this->sender ?? $envelope->getSender()->getAddress();

        $response = Http::withToken($this->token())
            ->post('https://graph.microsoft.com/v1.0/users/'.rawurlencode($sender).'/sendMail', [
                'message' => $this->payload($email, $envelope),
                'saveToSentItems' => $this->saveToSentItems,
            ]);

        if ($response->failed()) {
            throw new TransportException('Microsoft Graph could not send the message: '.$this->error($response), $response->status());
        }
    }

    /**
     * Access token for Graph, cached until shortly before it expires.
     */
    protected function token(): string
    {
        $key = 'gemadigital:microsoft-graph:'.md5($this->tenantId.$this->clientId);

        $token = Cache::get($key);

        if (is_string($token)) {
            return $token;
        }

        $response = Http::asForm()->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
        ]);

        $token = $response->json('access_token');

        if ($response->failed() || ! is_string($token)) {
            throw new TransportException('Microsoft Graph did not give an access token: '.$this->error($response), $response->status());
        }

        $expiresIn = $response->json('expires_in');
        Cache::put($key, $token, max(60, (is_numeric($expiresIn) ? (int) $expiresIn : 3600) - 300));

        return $token;
    }

    /**
     * The message in the format of Graph's sendMail.
     *
     * @return array<string, mixed>
     */
    protected function payload(Email $email, Envelope $envelope): array
    {
        $html = $email->getHtmlBody();

        // Bcc isn't in the headers anymore: it's every envelope recipient that isn't in To or Cc
        $listed = array_map(fn (Address $address) => strtolower($address->getAddress()), [...$email->getTo(), ...$email->getCc()]);
        $bcc = array_filter($envelope->getRecipients(), fn (Address $address) => ! in_array(strtolower($address->getAddress()), $listed, true));

        return array_filter([
            'subject' => $email->getSubject(),
            'body' => [
                'contentType' => $html !== null ? 'HTML' : 'Text',
                'content' => $this->content($html ?? $email->getTextBody()),
            ],
            'from' => $this->recipient($email->getFrom()[0] ?? $envelope->getSender()),
            'toRecipients' => array_map($this->recipient(...), $email->getTo()),
            'ccRecipients' => array_map($this->recipient(...), $email->getCc()),
            'bccRecipients' => array_values(array_map($this->recipient(...), $bcc)),
            'replyTo' => array_map($this->recipient(...), $email->getReplyTo()),
            'attachments' => array_map($this->attachment(...), $email->getAttachments()),
        ]);
    }

    /**
     * @return array{emailAddress: array<string, string>}
     */
    protected function recipient(Address $address): array
    {
        return ['emailAddress' => array_filter([
            'address' => $address->getAddress(),
            'name' => $address->getName(),
        ])];
    }

    /**
     * Attachments and inline images (these keep the cid the HTML points to).
     *
     * @return array<string, bool|string>
     */
    protected function attachment(DataPart $part): array
    {
        $inline = $part->getDisposition() === 'inline';

        return array_filter([
            '@odata.type' => '#microsoft.graph.fileAttachment',
            'name' => $part->getFilename() ?? 'attachment',
            'contentType' => $part->getContentType(),
            'contentBytes' => base64_encode($part->getBody()),
            'isInline' => $inline,
            'contentId' => $inline ? $part->getContentId() : null,
        ], fn ($value) => $value !== null);
    }

    /**
     * @param  resource|string|null  $body
     */
    protected function content($body): string
    {
        return is_resource($body) ? (string) stream_get_contents($body) : (string) $body;
    }

    protected function error(Response $response): string
    {
        $message = $response->json('error.message') ?? $response->json('error_description');

        return is_string($message) ? $message : 'HTTP '.$response->status();
    }

    public function __toString(): string
    {
        return 'microsoft-graph';
    }
}
