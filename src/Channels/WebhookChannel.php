<?php

namespace CarroPublic\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Events\Dispatcher;
use CarroPublic\Notifications\Managers\SenderManager;
use CarroPublic\Notifications\Messages\WebhookMessage;
use CarroPublic\Notifications\Events\NotificationWasSent;

class WebhookChannel
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
        if (!method_exists($notification, 'toWebhook')) {
            throw new \InvalidArgumentException('toWebhook was missing in ' . get_class($notification));
        }
        $message = $notification->toWebhook($notifiable);

        if (! $message instanceof WebhookMessage) {
            throw new \InvalidArgumentException('toWebhook must return instance of CarroPublic\Notifications\Messages\WebhookMessage');
        }

        if (! $to = $notifiable->routeNotificationFor('webhook', $notification)) {
            if (! $to = $notifiable->routeNotificationFor(WebhookChannel::class, $notification)) {
                return;
            }
        }

        $sender = $this->manager->sender('webhook', $message->sender ?? null);
        $response = $sender->send($to, $message);

        if ($this->events) {
            $this->events->dispatch(
                new NotificationWasSent($response, $sender, $message)
            );
        }
    }
}
