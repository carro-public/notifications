<?php

namespace CarroPublic\Notifications\Channels;

use JsonResponse;
use Illuminate\Events\Dispatcher;
use CarroPublic\Notifications\Managers\SenderManager;
use CarroPublic\Notifications\Messages\TwilioMessage;
use CarroPublic\Notifications\Messages\TelerivetMessage;
use CarroPublic\Notifications\Events\NotificationWasSent;

class TelerivetChannel
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
        // Fetch the payload of message
        if (!method_exists($notification, 'toTelerivet')) {
            throw new \InvalidArgumentException('toTelerivet was missing in ' . get_class($notification));
        }
        $message = $notification->toTelerivet();

        if (!$message instanceof TelerivetMessage) {
            throw new \InvalidArgumentException('toTwilio must return instance of CarroPublic\Notifications\Messages\TelerivetMessage');
        }
        
        // Fetch the reception phone number
        if (! $to = $notifiable->routeForNotification('telerivet')) {
            if (! $to = $notifiable->routeForNotification(TelerivetChannel::class)) {
                return;
            }
        }

        $response = $this->manager->sender('telerivet', $message->sender ?? null)->send($to, $message);

        if ($this->events) {
            $this->events->dispatch(
                new NotificationWasSent($response, $message->data)
            );
        }
        
        return $response;
    }
}
