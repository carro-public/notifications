<?php

namespace CarroPublic\Notifications\Messages;

use Illuminate\Database\Eloquent\Model;

abstract class Message
{
    public $from;

    public $message;

    public $sender;

    public $data;

    public $shouldPersist = false;

    public $extraPayload = [];

    public function __construct($message)
    {
        $this->message = $message;
        $this->data = [];
    }

    /**
     * @param $from
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param $data
     * @return self
     */
    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $sender
     * @return self
     */
    public function sender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @param $sender
     * @return self
     */
    public function shouldPersist($shouldPersist)
    {
        $this->shouldPersist = $shouldPersist;

        return $this;
    }

    public function extraPayload($extraPayload)
    {
        $this->extraPayload = $extraPayload;

        return $this;
    }

    /**
     * Convert object to string for printing
     * @return string
     */
    public function toString()
    {
        return '';
    }

    public function getType()
    {
        return static::TYPE;
    }

    public function getMessageId()
    {
        return null;
    }

    /**
     * Deep transform to array
     * @param $data
     * @return array|mixed
     */
    protected function toArray($data)
    {
        if ($data instanceof Model) {
            return "Model: " . $data->getTable() . " ~ ID: " . $data->getQueueableId();
        }

        if (!is_array($data)) {
            return $data;
        }

        return array_map(function ($item) {
            return $this->toArray($item);
        }, $data);
    }
}
