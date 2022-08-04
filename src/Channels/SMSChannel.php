<?php

namespace CarroPublic\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Events\Dispatcher;
use CarroPublic\Notifications\Messages\SMSMessage;
use CarroPublic\Notifications\Messages\WhatsAppMessage;
use CarroPublic\Notifications\Managers\SenderManager;
use CarroPublic\Notifications\Events\NotificationWasSent;

class SMSChannel
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
        if (!method_exists($notification, 'toSMS')) {
            throw new \InvalidArgumentException('toSMS was missing in ' . get_class($notification));
        }
        $message = $notification->toSMS($notifiable);

        if (! $message instanceof SMSMessage) {
            throw new \InvalidArgumentException('toSMS must return instance of CarroPublic\Notifications\Messages\SMSMessage');
        }

        if (! $to = $notifiable->routeNotificationFor('sms', $notification)) {
            if (! $to = $notifiable->routeNotificationFor(SMSChannel::class, $notification)) {
                return;
            }
        }

        $sender = $this->manager->sender('sms', $message->sender ?? null);
        $messageInstance = $sender->send($to, $message);

        if ($this->events) {
            $this->events->dispatch(
                new NotificationWasSent($messageInstance, $sender, $message->data)
            );
        }
    }
}
