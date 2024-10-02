<?php

namespace CarroPublic\Notifications\Messages;

class SMSMessage extends Message
{
    const TYPE = 'sms';
    
    /**
     * Convert object to string for printing
     * @return false|string
     */
    public function toString()
    {
        return json_encode([
            "From" => $this->from,
            "Message" => $this->message,
            "Sender" => $this->sender ?? 'default',
            "Data" => $this->toArray($this->data),
        ], JSON_PRETTY_PRINT);
    }
}
