<?php

namespace CarroPublic\Notifications\Senders;

use Twilio\Rest\Api;
use Twilio\Rest\Client;
use Twilio\Rest\Api\V2010;
use InvalidArgumentException;
use Illuminate\Events\Dispatcher;
use CarroPublic\Notifications\Messages\Message;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use CarroPublic\Notifications\Messages\WhatsAppMessage;

class TwilioSender extends Sender
{
    /**
     * Twilio Client
     *
     * @var Client
     */
    protected $client;

    /**
     * Initialize Twilio account sid and auth token
     */
    public function __construct($config, Dispatcher $events, $logger)
    {
        parent::__construct($config, $events, $logger);
        if (empty($config['account_sid']) || empty($config['auth_token'])) {
            throw new InvalidArgumentException('Missing account_sid or auth_token for TwilioSender in config/notifications.php');
        }

        $this->client = new Client($config['account_sid'], $config['auth_token']);
    }

    /**
     * @param $to
     * @return MessageInstance[]
     */
    public function send($to, $message)
    {
        $message->from($this->getFrom($message));

        $payloads = $this->generatePayloads($message);

        return array_map(function ($payload) use ($to, $message) {
            return $this->sendPayload($payload, $to, $message);
        }, $payloads);
    }

    /**
     * Send payload to twilio and receive message instance
     * @param $payload
     * @param $to
     * @param Message $message
     * @return MessageInstance|MessageInstance
     */
    protected function sendPayload($payload, $to, Message $message)
    {
        if (!parent::send($to, $message)) {
            return new MessageInstance(new V2010(new Api($this->client)), array_merge([
                'sid' => 'rejected_event_dispatched'
            ], $payload), $this->config['account_sid']);
        }

        $isWhatsApp = $message instanceof WhatsAppMessage;

        return $this->client->messages->create(
            $isWhatsApp ? "whatsapp:" . $to : $to,
            $payload
        );
    }

    /**
     * Generate all Twilio Payloads to call API
     * @param WhatsAppMessage $message
     * @return array
     */
    protected function generatePayloads(Message $message): array
    {
        $payloads = [];
        if (!empty($message->message)) {
            $payloads['text'] = array_merge([
                'from' => $message->from,
                'body' => $message->message,
            ], $message->extraPayload);
        }
        
        if ($message instanceof WhatsAppMessage && !empty($message->mediaUrls)) {
            # Init media array for message instances
            foreach ($message->mediaUrls as $mediaUrl) {
                $key = data_get($mediaUrl, "id", data_get($mediaUrl, "url"));
                $payloads["media-{$key}"] = array_merge([
                    "from" => $message->from,
                    "body" => $mediaUrl["file_name"],
                    "mediaUrl" => $mediaUrl["url"],
                ], $message->extraPayload);
            }
        }

        return $payloads;
    }

    /**
     * Get default from phone number
     * @return mixed
     */
    public function getDefaultFrom()
    {
        return data_get($this->config, 'from', data_get($this->config, 'default.from'));
    }

    /**
     * In case of whatsapp, append whatsapp: as prefix
     * @param Message $message
     * @return string
     */
    protected function getFrom(Message $message)
    {
        $isWhatsApp = $message instanceof WhatsAppMessage;
        $from = $message->from ?? $this->getDefaultFrom();

        return $isWhatsApp ? ("whatsapp:" . $from) : $from;
    }
}
