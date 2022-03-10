<?php

namespace CarroPublic\Notifications\Messages;

class LineMessage extends Message
{
    /**
     * @var
     */
    public $attachments = [];

    /**
     * @param mixed $attachments
     */
    public function attachments($attachments)
    {
        $this->attachments = $attachments;
        
        return $this;
    }

    /**
     * Convert object to string for printing
     * @return false|string
     */
    public function toString()
    {
        return json_encode([
            "Message" => $this->message,
            "Data" => $this->toArray($this->data),
            "Attachments" => $this->toArray($this->attachments),
        ], JSON_PRETTY_PRINT);
    }
}
