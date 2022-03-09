<?php

namespace CarroPublic\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Events\Dispatcher;
use CarroPublic\Notifications\Messages\TwilioMessage;
use CarroPublic\Notifications\Managers\SenderManager;
use CarroPublic\Notifications\Events\NotificationWasSent;

class TwilioChannel
{
    protected $manager;
    
    protected $events;

    public function __construct(SenderManager $manager, Dispatcher $events = null)
    {
        $this->manager = $manager;
        $this->events = $events;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toTwilio')) {
            throw new \InvalidArgumentException('toTwilio was missing in ' . get_class($notification));
        }
        $message = $notification->toTwilio($notifiable);

        if (! $message instanceof TwilioMessage) {
            throw new \InvalidArgumentException('toTwilio must return instance of CarroPublic\Notifications\Messages\TwilioMessage');
        }

        if (! $to = $notifiable->routeNotificationFor('twilio', $notification)) {
            if (! $to = $notifiable->routeNotificationFor(TwilioChannel::class, $notification)) {
                return;
            }
        }

        $messageInstance = $this->manager->sender('twilio', $message->sender ?? null)->send($to, $message);

        if ($this->events) {
            $this->events->dispatch(
                new NotificationWasSent($messageInstance, $message->data)
            );
        }
    }
}
