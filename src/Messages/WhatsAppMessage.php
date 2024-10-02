<?php

namespace CarroPublic\Notifications\Messages;

class WhatsAppMessage extends Message
{
    const TYPE = 'whatsapp';
    
    /**
     * @var
     */
    public $mediaUrls;

    /**
     * @var bool
     */
    public $isWhatsApp;

    /**
     * @param mixed $mediaUrl
     */
    public function mediaUrls($mediaUrls)
    {
        $this->mediaUrls = $mediaUrls;

        return $this;
    }

    /**
     * @param bool $isWhatsApp
     */
    public function setAsWhapsApp()
    {
        $this->isWhatsApp = true;

        return $this;
    }

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
            "Type" => $this->isWhatsApp ? 'WhatsApp' : 'SMS',
            "Data" => $this->toArray($this->data),
            "MediaUrls" => $this->mediaUrls,
        ], JSON_PRETTY_PRINT);
    }
}
