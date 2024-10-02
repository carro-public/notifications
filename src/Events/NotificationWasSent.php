<?php

namespace CarroPublic\Notifications\Events;

use CarroPublic\Notifications\Senders\Sender;
use CarroPublic\Notifications\Messages\Message;

class NotificationWasSent
{
    /**
     * The SMS message instance.
     */
    public $message;

    /**
     * The message data.
     *
     * @var Message
     */
    public Message $originalMessage;

    /**
     * @var Sender
     */
    public $sender;

    /**
     * Create a new event instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct($message, $sender, $originalMessage)
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->originalMessage = $originalMessage;
    }
}
