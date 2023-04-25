<?php

namespace CarroPublic\Notifications\Senders;

use Psr\Log\LoggerInterface;
use Illuminate\Events\Dispatcher;
use CarroPublic\Notifications\Messages\Message;
use CarroPublic\Notifications\Events\MessageRejectedForSandbox;

abstract class Sender
{
    /**
     * Credentials array
     * @var array
     */
    protected $config;

    /**
     * @var bool Determine if running in sandbox mode
     */
    protected $sandbox;
    
    protected $events;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Closure Callback to determine if phone number is valid for sandbox
     */
    public static $validForSandboxValidator;

    public static $runningInSandboxValidator;

    public function __construct($config, Dispatcher $events, LoggerInterface $logger) {
        $this->sandbox = $this->isRunningInSandbox();
        $this->config = $config;
        $this->events = $events;
        $this->logger = $logger;
    }
    
    /**
     * Return false when message is not valid to send
     * @param $to
     * @param Message $message
     * @return bool|mixed
     */
    public function send($to, Message $message)
    {
        if ($this->sandbox && !$this->isValidForSandbox($to, $message)) {
            return false;
        }
        
        return true;
    }

    /**
     * @param $to
     * @param $message
     * @return false|mixed
     */
    public function isValidForSandbox($to, $message)
    {
        $isValid = false;

        if (self::$validForSandboxValidator) {
            $isValid = call_user_func(self::$validForSandboxValidator, $to, self::class);
        }

        if (!$isValid && $this->events) {
            $this->events->dispatch(new MessageRejectedForSandbox($to, $message));
        }

        return $isValid;
    }

    /**
     * This closure will be called to determine if recipient address is valid for sandbox
     * @param $validator
     * @return void
     */
    public static function registerValidForSandbox($validator)
    {
        self::$validForSandboxValidator = $validator;
    }

    /**
     * Register sandbox Mode Validator
     * @param \Closure $closure
     * @return void
     */
    public static function registerSandboxValidator(\Closure $closure)
    {
        self::$runningInSandboxValidator = $closure;
    }

    /**
     * Execute sandbox mode closure or return false
     * @return false
     */
    protected function isRunningInSandbox()
    {
        if (!is_null(self::$runningInSandboxValidator)) {
            return call_user_func(self::$runningInSandboxValidator);
        }

        return false;
    }
}
