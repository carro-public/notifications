<?php

namespace CarroPublic\Notifications\Models;

use CarroPublic\Notifications\Events\NotificationWasSent;

class NullNotificationModelProvider extends NotificationModelProvider 
{
    /**
     * Do nothing
     * @param $data
     * @return null
     */
    public function makeModelForMessage(NotificationWasSent $event, $model = null)
    {
        return $model;
    }
}
