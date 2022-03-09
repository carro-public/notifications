<?php

namespace CarroPublic\Notifications\Senders;

use Twilio\Rest\Api;
use Twilio\Rest\Client;
use Twilio\Rest\Api\V2010;
use InvalidArgumentException;
use CarroPublic\Notifications\Messages\Message;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use CarroPublic\Notifications\Messages\TwilioMessage;

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
    public function __construct($config)
    {
        parent::__construct($config);
        if (empty($config['account_sid']) || empty($config['auth_token'])) {
            throw new InvalidArgumentException('Missing account_sid or auth_token for TwilioSender in config/notifications.php');
        }

        $this->client = new Client($config['account_sid'], $config['auth_token']);
    }

    /**
     * @param $to
     */
    public function send($to, $message)
    {
        $payload = [
            'from' => $this->getFrom($message),
            'body' => $message->message,
        ];
        
        if (!parent::send($to, $message)) {
            return new MessageInstance(new V2010(new Api($this->client)), array_merge([
                'sid' => 'rejected_event_dispatched'
            ], $payload), $this->config['account_sid']);
        }
        
        if (!empty($message->mediaUrls)) {
            $payload['mediaUrl'] = $message->mediaUrls;
        }

        return $this->client->messages->create(
            $message->isWhatsApp ? "whatsapp:" . $to : $to,
            $payload
        );
    }

    /**
     * Get default from phone number
     * @return mixed
     */
    public function getDefaultFrom($whatsApp)
    {
        if ($whatsApp) {
            return data_get($this->config, 'whatsapp_from', data_get(
                $this->config,
                'default.whatsapp_from'
            ));
        }

        return data_get($this->config, 'from', data_get($this->config, 'default.from'));
    }

    /**
     * In case of whatsapp, append whatsapp: as prefix
     * @param Message $message
     * @return string
     */
    protected function getFrom(Message $message)
    {
        $from = $message->from ?? $this->getDefaultFrom($message->isWhatsApp);

        return $message->isWhatsApp ? ("whatsapp:" . $from) : $from;
    }
}
