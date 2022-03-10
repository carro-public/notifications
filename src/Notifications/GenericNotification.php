<?php

namespace CarroPublic\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use InvalidArgumentException;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use CarroPublic\Notifications\Messages\SMSMessage;
use CarroPublic\LaravelTwilio\LaravelTwilioMessage;
use CarroPublic\Notifications\Messages\MailMessage;
use CarroPublic\Notifications\Messages\LineMessage;
use CarroPublic\Notifications\Messages\WhatsAppMessage;

class GenericNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $body;

    protected $channel;

    protected $from;
    
    protected $sender;
    
    protected $data;
    
    protected $subject;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($body, $channel = null, $data = [])
    {
        $this->body = $body;
        $this->channel = $channel;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return array_keys($notifiable->routes);
        }

        if (is_null($this->channel)) {
            throw new InvalidArgumentException('There is no specified channel to send.');
        }

        return is_array($this->channel) ? $this->channel : [$this->channel];
    }
    
    public function toMail($notifiable) {
        $mailMessage = (new \Illuminate\Notifications\Messages\MailMessage())
            ->view('notifications::mail', array_merge($this->data, ['body' => $this->body]))
            ->subject($this->subject)
            ->mailer($this->sender);
        if ($this->from) {
            $mailMessage->from($this->from);
        }
        return $mailMessage;
    }

    public function toSMS($notifiable)
    {
        return (new SMSMessage($this->body))->data($this->data)->from($this->from)->sender($this->sender);
    }

    public function toSMS2Way($notifiable)
    {
        return (new SMSMessage($this->body))->data($this->data)->from($this->from)->sender($this->sender);
    }

    public function toWhatsApp($notifiable)
    {
        return (new WhatsAppMessage($this->body))->data($this->data)->from($this->from)->sender($this->sender);
    }

    public function toLine($notifiable) {
        return (new LineMessage($this->body))->data($this->data)->from($this->from)->sender($this->sender);
    }

    /**
     * @param string $from
     * @return self
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param $sender
     * @return self
     */
    public function sender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @param mixed $subject
     */
    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }
}
