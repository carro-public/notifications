<?php

namespace CarroPublic\Notifications\Senders;

use Illuminate\Support\Facades\Http;
use CarroPublic\Notifications\Messages\Message;
use CarroPublic\Notifications\Messages\WebhookMessage;

class WebhookSender extends Sender
{
    /**
     * Return false when message is not valid to send
     * @param $to
     * @param WebhookMessage $message
     * @return bool|mixed
     */
    public function send($to, Message $message)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::{$message->method}($message->endpoint, $message->payload);
        
        if ($response->failed()) {
            throw new \Exception($response->body());
        }
        
        return $response;
    }
}
