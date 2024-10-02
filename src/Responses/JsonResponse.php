<?php

namespace CarroPublic\Notifications\Responses;

class JsonResponse
{
    protected $data;
    
    public function __construct($data) {
        $this->data = json_decode($data);
    }
    
    public function getJSONDecodedBody()
    {
        return $this->data;
    }
    
    public function __get($name)
    {
        return data_get($this->data, $name);
    }
}
