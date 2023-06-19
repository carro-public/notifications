<?php

namespace CarroPublic\Notifications\Messages;

class WhatsAppMessage extends Message
{
    /**
     * @var
     */
    public $mediaUrls;

    /**
     * @var bool
     */
    public $isWhatsApp;
    
    public $contentSid = null;
    
    public $contentVariables = [];

    /**
     * @param mixed $mediaUrl
     */
    public function mediaUrls($mediaUrls)
    {
        $this->mediaUrls = $mediaUrls;

        return $this;
    }

    /**
     * @param $contentSid
     * @return $this
     */
    public function contentSid($contentSid)
    {
        $this->contentSid = $contentSid;
        
        return $this;
    }

    /**
     * @param $contentVariables
     * @return $this
     */
    public function contentVariables($contentVariables)
    {
        $this->contentVariables = $contentVariables;

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
