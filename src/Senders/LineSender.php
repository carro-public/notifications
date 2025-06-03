<?php

namespace CarroPublic\Notifications\Senders;

use LINE\LINEBot;
use LINE\LINEBot\Response;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use CarroPublic\Notifications\Messages\Message;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use CarroPublic\Notifications\Messages\LineMessage;
use CarroPublic\Notifications\Responses\JsonResponse;
use LINE\LINEBot\Exception\InvalidEventRequestException;

class LineSender extends Sender
{
    protected $client;

    public function __construct($config, $events, $logger)
    {
        parent::__construct($config, $events, $logger);
        if (empty($config['secret']) || empty($config['token'])) {
            throw new InvalidArgumentException('Missing secret or token for LineSender in config/notifications.php');
        }

        $httpClient = new CurlHTTPClient($config['token']);
        $this->client = new LINEBot($httpClient, ['channelSecret' => $config['secret']]);
    }

    /**
     * @throws \Exception
     */
    public function send($to, $message)
    {
        if (!parent::send($to, $message)) {
            return false;
        }

        $payloads = $this->generatePayloads($message);

        $responses = collect(array_map(function ($payload) use ($to) {
            return $this->client->pushMessage($to, $payload, true);
        }, $payloads));

        $errorResponses = $responses->where(function (Response $response) {
            return !$response->isSucceeded();
        });
        if ($errorResponses->isNotEmpty()) {
            $message = $errorResponses->map(function (Response $response) {
                return data_get($response->getJSONDecodedBody(), 'message');
            })->implode(function($value, $key) {
                return "{$key}: {$value}";
            }, PHP_EOL);
            
            throw new \Exception($message);
        }
    }

    /**
     * @param LineMessage $message
     * @return array
     */
    protected function generatePayloads(Message $message)
    {
        $payloads = [];

        if (!empty($message->message)) {
            $payloads['text'] = new TextMessageBuilder($message->message);
        }

        /** @var LINEBot\MessageBuilder $attachment */
        foreach ($message->attachments as $index => $attachment) {
            $key = data_get(Arr::first($attachment->buildMessage()), "originalContentUrl", $index);
            $payloads["media-{$key}"] = $attachment;
        };

        return $payloads;
    }
}
