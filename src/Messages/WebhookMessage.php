<?php

namespace CarroPublic\Notifications\Messages;

class WebhookMessage extends Message
{
    public $method = 'POST';
    
    public $payload = [];
    
    public $endpoint;

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        
        return $this;
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        
        return $this;
    }

    /**
     * @param mixed $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        
        return $this;
    }
}
