<?php

namespace CarroPublic\Notifications\Models;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use LINE\LINEBot\Response as LineResponse;
use CarroPublic\Notifications\Senders\Sender;
use CarroPublic\Notifications\Messages\Message;
use CarroPublic\Notifications\Senders\LineSender;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use CarroPublic\Notifications\Senders\InfobipSender;
use CarroPublic\Notifications\Responses\InfobipResponse;
use CarroPublic\Notifications\Events\NotificationWasSent;
use CarroPublic\Notifications\Responses\TelerivetResponse;

class NotificationModelProvider
{
    /**
     * @var NotificationModel
     */
    protected $model;
    
    /**
     * The callback that may modify the model retrieval queries.
     *
     * @var (\Closure(\Illuminate\Database\Eloquent\Builder):mixed)|null
     */
    protected $queryCallback;

    public function __construct($model = null)
    {
        $this->model = $model ?? config('notifications.model.eloquent');
    }

    /**
     * @param $messageId
     * @return Model|NotificationModel|null
     */
    public function getModelByMessageId($messageId)
    {
        return $this->newModelQuery()->where(
            $this->createModel()->getMessageIdFieldName(), $messageId
        )->first();
    }

    /**
     * @param Sender $sender
     * @param $message
     * @param Message $originalMessage
     * @param null $model
     * @return Builder|NotificationModel
     */
    public function makeModelForMessage(NotificationWasSent $event, $model = null)
    {
        $message = $event->message;
        $originalMessage = $event->originalMessage;
        $sender = $event->sender;
        
        /** @var NotificationModel $model */
        if (empty($model)) {
            $model = $this->newModelQuery()->make();
        }
        
        $data = [
            $model->getBodyFieldName() => $event->originalMessage->message,
            $model->getMessageIdFieldName() => $this->getMessageId($event),
            $model->getFromFieldName() => $this->getFrom($event),
            $model->getTypeFieldName() => $originalMessage->getType(),
            $model->getMessageServiceFieldName() => $sender->getSenderName(),
        ];
        
        return $model->fill($data);
    }
    
    /**
     * @param \Closure|null $queryCallback
     * @return $this
     */
    public function setQueryCallback(?\Closure $queryCallback)
    {
        $this->queryCallback = $queryCallback;

        return $this;
    }

    /**
     * Get a new query builder for the model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newModelQuery($model = null)
    {
        $query = is_null($model)
            ? $this->createModel()->newQuery()
            : $model->newQuery();

        with($query, $this->queryCallback);

        return $query;
    }

    /**
     * @return NotificationModel
     */
    protected function createModel()
    {
        return new $this->model;
    }

    /**
     * @param NotificationWasSent $message
     * @return array|mixed|string|null
     */
    protected function getMessageId(NotificationWasSent $event)
    {
        $message = $event->message;
        
        return match (true) {
            get_class($message) === MessageInstance::class => $message->sid,
            get_class($message) === LineResponse::class => data_get($message->getJSONDecodedBody(), 'messages.0.id'),
            get_class($message) === InfobipResponse::class => data_get($message->getJSONDecodedBody(), 'messages.0.messageId'),
            get_class($message) === TelerivetResponse::class => data_get($message->getJSONDecodedBody(), 'messages.0.id'),
            get_class($message) === '\Symfony\Component\Mime\Message' => $message->getMessageId(),
            get_class($message) === '\Symfony\Component\Mime\Email' => $message->generateMessageId(),
            default => null,
        };
    }

    /**
     * @param $message
     * @return string
     */
    protected function getFrom(NotificationWasSent $event)
    {
        $message = $event->message;
        $sender = $event->sender;
        
        return match (true) {
            $sender instanceof InfobipSender => $sender->getFrom(),
            $sender instanceof LineSender => $sender->getSenderName(),
            get_class($message) === MessageInstance::class => Arr::last(explode(":", $message->from)),
            get_class($message) === '\Symfony\Component\Mime\Message' => array_key_first($message->getFrom()),
            get_class($message) === '\Symfony\Component\Mime\Email' => Arr::first($message->getFrom())->getAddress(),
            default => null,
        };
    }
}
