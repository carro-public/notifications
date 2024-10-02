<?php

namespace CarroPublic\Notifications\Models;

trait NotificationModel
{
    /**
     * @return string
     */
    public function getMessageIdFieldName(): string
    {
        return defined('static::MESSAGE_ID') ? static::MESSAGE_ID : 'message_id';
    }

    /**
     * @return string
     */
    public function getBodyFieldName(): string
    {
        return defined('static::BODY') ? static::BODY : 'body';
    }

    /**
     * @return string
     */
    public function getFromFieldName(): string
    {
        return defined('static::FROM') ? static::FROM : 'from';
    }

    /**
     * @return string
     */
    public function getTypeFieldName(): string
    {
        return defined('static::TYPE') ? static::TYPE : 'type';
    }

    /**
     * @return string
     */
    public function getMessageServiceFieldName(): string
    {
        return defined('static::MESSAGE_SERVICE') ? static::MESSAGE_SERVICE : 'message_service';
    }
}
