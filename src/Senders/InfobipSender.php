<?php

namespace CarroPublic\Notifications\Senders;

use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use CarroPublic\Notifications\Messages\Message;
use CarroPublic\Notifications\Responses\JsonResponse;
use CarroPublic\Notifications\Responses\InfobipResponse;

class InfobipSender extends Sender
{
    /**
     * @var string $baseUrl
     */
    private $baseUrl;

    /**
     * @var string $apiKey
     */
    private $apiKey;

    /**
     * @var string $projectId
     */
    private $projectId;

    /**
     * @var string $from
     */
    private $from;

    /**
     * Setting Telerivet API key and project id
     */
    public function __construct($config, $events, $logger)
    {
        parent::__construct($config, $events, $logger);
        if (empty($config['api_key']) || empty($config['project_id'] || empty($config['from']))) {
            throw new InvalidArgumentException('Missing api_key or project_id or from for InfobipSender in config/notifications.php');
        }

        $this->baseUrl = $config['base_url'];
        $this->apiKey = $config['api_key'];
        $this->projectId = $config['project_id'];
        $this->from = $config['from'];
    }

    public function send($to, Message $message)
    {
        if (!parent::send($to, $message)) {
            return json_encode([]);
        }

        $response = Http::withHeaders([
            'Authorization' => "App {$this->apiKey}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/sms/{$this->projectId}/text/advanced", [
            'messages' => [
                [
                    'destinations' => [
                        [
                            'to' => $to
                        ],
                    ],
                    'from' => $this->from,
                    'text' => $message->message,
                ]
            ]
        ]);


        return new InfobipResponse($response->body());
    }
    
    public function getFrom()
    {
        return $this->from;
    }
}
