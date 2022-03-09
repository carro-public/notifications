<?php

namespace CarroPublic\Notifications\Events;

class MessageRejectedForSandbox
{
    /**
     * The SMS message instance.
     */
    public $message;

    /**
     * Receive phone number
     * @var
     */
    public $to;

    /**
     * Create a new event instance.
     *
     * @param $to
     */
    public function __construct($to, $message)
    {
        $this->to = $to;
        $this->message = $message;
    }
}
