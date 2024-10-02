<?php

namespace CarroPublic\Notifications\Channels;

use Illuminate\Mail\Message;
use Illuminate\Mail\Markdown;
use Illuminate\Events\Dispatcher;
use Symfony\Component\Mime\Email;
use Illuminate\Contracts\View\Factory;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Part\TextPart;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Config\Repository;
use CarroPublic\Notifications\Senders\Sender;
use CarroPublic\Notifications\Senders\MailSender;
use CarroPublic\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use CarroPublic\Notifications\Events\NotificationWasSent;
use CarroPublic\Notifications\Events\MessageRejectedForSandbox;

class MailChannel extends \Illuminate\Notifications\Channels\MailChannel
{
    protected $events;
    
    protected $view;
    
    protected $config;
    
    public function __construct(MailFactory $mailer, Markdown $markdown, Dispatcher $events, Factory $view, Repository $config)
    {
        parent::__construct($mailer, $markdown);
        $this->events = $events;
        $this->view = $view;
        $this->config = $config;
    }

    /**
     * @param $notifiable
     * @param Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('mail', $notification)) {
            if (! $to = $notifiable->routeNotificationFor(MailChannel::class, $notification)) {
                return;
            }
        }
        
        $sandbox = !empty(Sender::$runningInSandboxValidator) ? call_user_func(Sender::$runningInSandboxValidator) : false;
        $valid = !empty(Sender::$validForSandboxValidator) ? call_user_func(Sender::$validForSandboxValidator, $to, MailChannel::class) : true;
        
        if ($sandbox && !$valid && $this->events) {
            $mailMessage = $notification->toMail($notifiable);
            # Only process message building from view
            if (is_string($mailMessage->view)) {
                $content = $this->view->make($mailMessage->view, $mailMessage->viewData)->render();
            } else {
                $content = json_encode($mailMessage->view);
            }
            
            $message = new MailMessage($content);
            $message->data($mailMessage->viewData)
                ->sender($mailMessage->mailer)
                ->subject($mailMessage->subject)
                ->from($this->getFrom($mailMessage))
                ->cc($mailMessage->cc);
            $this->events->dispatch(new MessageRejectedForSandbox($to, $message));
            $this->events->dispatch(new NotificationWasSent(
                new Message((new Email())->subject($mailMessage->subject)->setBody(new TextPart($content))),
                $mailMessage,
                $message,
            ));
            
            return;
        }
        
        $this->events->listen(MessageSent::class, function (MessageSent $event) {
            $this->events->dispatch(new NotificationWasSent(
                $event->message,
                new MailSender([], $this->events, app('log')),
                $event
            ));
        });
        
        parent::send($notifiable, $notification);
    }

    /**
     * @param $mailMessage
     * @return int|string|void|null
     */
    protected function getFrom($mailMessage) {
        return array_key_first($mailMessage->from) ??
            data_get($this->config->get("mail.mailers.{$mailMessage->mailer}.from"), 'address');
    }
}
