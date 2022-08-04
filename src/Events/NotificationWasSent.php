<?php

namespace CarroPublic\Notifications\Events;

use CarroPublic\Notifications\Senders\Sender;

class NotificationWasSent
{
    /**
     * The SMS message instance.
     */
    public $message;

    /**
     * The message data.
     *
     * @var array
     */
    public $data;

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
    public function __construct($message, $sender, $data = [])
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->data = $data;
    }
}
