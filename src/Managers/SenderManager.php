<?php

namespace CarroPublic\Notifications\Managers;

use InvalidArgumentException;
use CarroPublic\Notifications\Senders\Sender;
use CarroPublic\Notifications\Senders\Factory;
use CarroPublic\Notifications\Senders\LineSender;
use CarroPublic\Notifications\Senders\TwilioSender;
use CarroPublic\Notifications\Senders\TelerivetSender;

class SenderManager implements Factory
{
    /**
     * The array of resolved senders.
     *
     * @var array
     */
    protected $senders = [];

    protected $config;

    public function __construct($app)
    {
        $this->config = $app['config'];
    }

    /**
     * Get a sender instance by name.
     *
     * @param string $name
     * @return Sender
     */
    public function sender($channel, $name = null)
    {
        return $this->senders[$channel][$name] ?? $this->resolve($channel, $name);
    }

    /**
     * Resolve the given sender.
     *
     * @param string $name
     * @return Sender
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($channel, $name)
    {
        $config = $this->getConfig($channel, $name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Channel [{$channel}] with sender [{$name}] is not defined in config.");
        }

        if (!method_exists($this, $method = 'create' . ucfirst($channel) . 'Channel')) {
            throw new InvalidArgumentException("Unsupported transport [{$channel}] for service .");
        }
        
        return $this->{$method}($config);
    }

    protected function createTwilioChannel($config)
    {
        return new TwilioSender($config);
    }

    protected function createTelerivetChannel($config)
    {
        return new TelerivetSender($config);
    }

    /**
     * @param $config
     * @return LineSender
     */
    protected function createLineChannel($config)
    {
        return new LineSender($config);
    }

    /**
     * Get the mail connection configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig($channel, $name = null)
    {
        if (is_null($name)) {
            return $this->config->get("{$channel}.default");
        }

        return $this->config->get("{$channel}.senders.{$name}");
    }
}
