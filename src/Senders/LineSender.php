<?php

namespace CarroPublic\Notifications\Senders;

use LINE\LINEBot;
use JsonResponse;
use InvalidArgumentException;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LineSender extends Sender
{
    protected $client;
    
    public function __construct($config)
    {
        parent::__construct($config);
        if (empty($config['secret']) || empty($config['token'])) {
            throw new InvalidArgumentException('Missing secret or token for LineSender in config/notifications.php');
        }
        
        $httpClient = new CurlHTTPClient($config['token']);
        $this->client = new LINEBot($httpClient, ['channelSecret' => $config['secret']]);
    }

    public function send($to, $message)
    {
        if (!parent::send($to, $message)) {
            return false;
        }
        
        $text = new TextMessageBuilder($message->message);

        $response = $this->client->pushMessage($to, $text, true);

        foreach ($message->attachments as $attachment) {
            $this->client->pushMessage($to, $attachment, true);
        }

        return new JsonResponse($response);
    }
}
