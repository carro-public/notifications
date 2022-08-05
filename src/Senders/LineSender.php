<?php

namespace CarroPublic\Notifications\Senders;

use LINE\LINEBot;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use CarroPublic\Notifications\Responses\JsonResponse;
use LINE\LINEBot\Exception\InvalidEventRequestException;

class LineSender extends Sender
{
    protected $client;
    
    public function __construct($config, $events)
    {
        parent::__construct($config, $events);
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
        
        if ($response->getHTTPStatus() !== 200) {
            throw new InvalidEventRequestException($response->getRawBody());
        }

        foreach ($message->attachments as $attachment) {
            $response = $this->client->pushMessage($to, $attachment, true);

            if ($response->getHTTPStatus() !== 200) {
                throw new InvalidEventRequestException($response->getRawBody());
            }
        }

        return new JsonResponse(json_encode([
            'id' => $response->getHeader('x-line-request-id')
        ]));
    }
}
