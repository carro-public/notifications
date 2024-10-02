<?php

namespace CarroPublic\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Events\Dispatcher;
use CarroPublic\Notifications\Messages\SMSMessage;
use CarroPublic\Notifications\Messages\WhatsAppMessage;
use CarroPublic\Notifications\Managers\SenderManager;
use CarroPublic\Notifications\Events\NotificationWasSent;

class WhatsAppChannel
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
        if (!method_exists($notification, 'toWhatsapp')) {
            throw new \InvalidArgumentException('toWhatsapp was missing in ' . get_class($notification));
        }
        $message = $notification->toWhatsapp($notifiable);

        if (! $message instanceof WhatsAppMessage) {
            throw new \InvalidArgumentException('toWhatsapp must return instance of CarroPublic\Notifications\Messages\WhatsAppMessage');
        }

        if (! $to = $notifiable->routeNotificationFor('whatsapp', $notification)) {
            if (! $to = $notifiable->routeNotificationFor(WhatsAppChannel::class, $notification)) {
                return;
            }
        }

        $sender = $this->manager->sender('whatsapp', $message->sender ?? null);
        $messageInstance = $sender->send($to, $message);

        if ($this->events) {
            $this->events->dispatch(
                new NotificationWasSent($messageInstance, $sender, $message)
            );
        }
    }
}
