<?php

namespace CarroPublic\Notifications\Senders;

use JsonResponse;
use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use CarroPublic\Notifications\Messages\Message;

class TelerivetSender extends Sender
{
    /**
     * @var string $apiKey
     */
    private $apiKey;

    /**
     * @var string $projectId
     */
    private $projectId;

    /**
     * Setting Telerivet API key and project id
     */
    public function __construct($config)
    {
        parent::__construct($config);
        if (empty($config['api_key']) || empty($config['project_id'] || empty($config['number']))) {
            throw new InvalidArgumentException('Missing api_key or project_id or number for TelerivetSender in config/notifications.php');
        }
        
        $this->apiKey = $config['api_key'];
        $this->projectId = $config['project_id'];
    }

    public function send($to, Message $message)
    {
        if (!parent::send($to, $message)) {
            return json_encode([]);
        }

        $response =  Http::post("https://api.telerivet.com/v1/projects/{$this->projectId}/messages/send", [
            'api_key' => $this->apiKey,
            'content' => $message->message,
            'to_number' => $to,
        ]);

        return new JsonResponse($response->body());
    }
}
