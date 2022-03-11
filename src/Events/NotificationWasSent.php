<?php

namespace CarroPublic\Notifications\Events;

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
     * Create a new event instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct($message, $data = [])
    {
        $this->message = $message;
        $this->data = $data;
    }
}
