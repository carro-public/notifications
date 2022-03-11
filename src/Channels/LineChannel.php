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
     * @return void
     */
    public function send($notifiable, $notification)
    {
        if (!method_exists($notification, 'toLine')) {
            throw new \InvalidArgumentException('toLine was missing in ' . get_class($notification));
        }
        $message = $notification->toLine($notifiable);

        if (!$message instanceof LineMessage) {
            throw new \InvalidArgumentException('toLine() must return CarroPublic\Notifications\Messages\LineMessage instance');
        }

        // Fetch recipient line id
        if (! $to = $notifiable->routeNotificationFor('line')) {
            if (! $to = $notifiable->routeNotificationFor(LineChannel::class)) {
                return;
            }
        }

        $response = $this->manager->sender('line')->send($to, $message);

        if ($this->events) {
            $this->events->dispatch(new NotificationWasSent($response, $message->data));
        }

        return $response;
    }
}
