<?php

namespace CarroPublic\Notifications\Subscribers;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use CarroPublic\Notifications\Senders\Sender;
use CarroPublic\Notifications\Messages\Message;
use CarroPublic\Notifications\Events\NotificationWasSent;
use CarroPublic\Notifications\Models\NotificationModelProvider;

class NotificationEventSubscribers
{
    public function onNotificationWasSent(NotificationWasSent $event)
    {
        /** @var Message $originalMessage */
        $originalMessage = $event->originalMessage;
        /** @var mixed $message */
        $message = $event->message;

        // If message should not be persisted
        if (!$originalMessage->shouldPersist) {
            return;
        }
        
        // If the messages is not supported
        if (!in_array(get_class($originalMessage), config('notifications.model.supported_messages', []))) {
            return;
        }
        
        $instance = data_get($message->data, 'model');
        /** @var NotificationModelProvider $provider */
        $provider = Container::getInstance()->make(NotificationModelProvider::class);
        $model = $provider->makeModelForMessage($event, $instance);
        
        if (config('notifications.model.quietly', false)) {
            $model->saveQuietly();
        } else {
            $model->save();
        }
    }
    
    public function subscribe(Dispatcher $events)
    {
        $events->listen(NotificationWasSent::class, [self::class, 'onNotificationWasSent']);
    }
}
