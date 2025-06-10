<?php

namespace CarroPublic\Notifications\Senders;

use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use CarroPublic\Notifications\Messages\Message;
use CarroPublic\Notifications\Responses\JsonResponse;

class ByteplusSender extends Sender
{
    /**
     * @var string $baseUrl
     */
    private $baseUrl;

    /**
     * @var string $username
     */
    private $username;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var string $from
     */
    private $from;

    public function __construct($config, $events, $logger)
    {
        parent::__construct($config, $events, $logger);
        
        if (empty($config['username']) || empty($config['password']) || empty($config['from'])) {
            throw new InvalidArgumentException('Missing username, password, or from for ByteplusSender in config/notifications.php');
        }

        $this->baseUrl = $config['base_url'] ?? 'https://sms.byteplusapi.com';
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->from = $config['from'];
    }

    public function send($to, Message $message)
    {
        if (!parent::send($to, $message)) {
            return json_encode([]);
        }

        try {
            $body = [
                'PhoneNumbers' => $to,
                'Content' => $message->message,
                'From' => $this->from,
            ];

            if (!empty($message->extraPayload['tag'])) {
                $body['Tag'] = $message->extraPayload['tag'];
            }

            if (!empty($message->extraPayload['callback_url'])) {
                $body['CallbackURL'] = $message->extraPayload['callback_url'];
            }

            $credentials = base64_encode($this->username . ':' . $this->password);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json;charset=utf-8',
                'Authorization' => 'Basic ' . $credentials,
            ])->post($this->baseUrl . '/sms/openapi/send_sms', $body);

            if ($response->failed()) {
                throw new \Exception('BytePlus SMS API request failed: ' . $response->body());
            }

            return new JsonResponse($response->body());
        } catch (\Exception $e) {
            throw $e;
        }
    }   
}