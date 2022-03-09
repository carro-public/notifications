<?php

namespace CarroPublic\Notifications\Channels;

use Illuminate\Events\Dispatcher;
use CarroPublic\Notifications\Messages\LineMessage;
use CarroPublic\Notifications\Managers\SenderManager;
use CarroPublic\Notifications\Events\NotificationWasSent;

class LineChannel
{
    protected $manager;
    
    protected $events;

    public function __construct(SenderManager $manager, Dispatcher $events)
    {
        $this->manager = $manager;
        $this->events = $events;
    }

    /**
     * @param $notifiable
     * @param $notification
     * @return bool
     */
    public function send($notifiable, $notification)
    {
        // Fetch recipient line id
        if (!$to = $notifiable->routeForNotification('line')) {
            if (!$to = $notifiable->routeForNotification(LineChannel::class)) {
                return false;
            }
        }

        // Fetch payload Line Message
        if (!method_exists($notification, 'toLine')) {
            return false;
        }

        $lineMessage = $notification->toLine();

        if (!$lineMessage instanceof LineMessage) {
            throw new \InvalidArgumentException(
                'toLine() must return CarroPublic\Notifications\Messages\LineMessage instance'
            );
        }

        $response = $this->manager->sender('line')->send($to, $lineMessage);

        if ($this->events) {
            $this->events->dispatch(new NotificationWasSent($response, $lineMessage->data));
        }

        return $response;
    }
}
