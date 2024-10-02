<?php

namespace CarroPublic\Notifications\Messages;

class MailMessage extends Message
{
    const TYPE = 'mail';
    
    public $subject;
    
    public $cc;

    /**
     * @param mixed $subject
     */
    public function subject($subject)
    {
        $this->subject = $subject;
        
        return $this;
    }

    /**
     * @param mixed $cc
     */
    public function cc($cc)
    {
        $this->cc = $cc;
        
        return $this;
    }
    
    public function toString()
    {
        return json_encode([
            "Message" => $this->message,
            "Sender" => $this->sender,
            "From" => $this->from,
            "Subject" => $this->subject,
            "CC" => $this->cc,
            "Data" => $this->toArray($this->data),
        ], JSON_PRETTY_PRINT);
    }
}
