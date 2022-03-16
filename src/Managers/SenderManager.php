<?php

namespace CarroPublic\Notifications\Managers;

use InvalidArgumentException;
use Illuminate\Events\Dispatcher;
use CarroPublic\Notifications\Senders\Sender;
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
    
    protected $events;

    public function __construct($app)
    {
        $this->config = $app['config'];
        $this->events = $app['events'];
    }

    /**
     * Get a sender instance by name.
     *
     * @param string $name
     * @return Sender
     */
    public function sender($service, $name = null)
    {
        return $this->senders[$service][$name] ?? $this->resolve($service, $name);
    }

    /**
     * Resolve the given sender.
     *
     * @param string $name
     * @return Sender
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($service, $name)
    {
        $config = $this->getConfig($service, $name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Service [{$service}] with sender [{$name}] is not defined in config.");
        }

        // Default transport of the service would be used when no transport was specified
        $transport = trim($config['transport'] ?? data_get($this->getConfig($service), 'transport'));
        
        // This service do not support multiple transports
        // Return Sender creation
        if (empty($transport)) {
            if (!method_exists($this, $serviceMethod = 'create' . ucfirst($service) . 'Service')) {
                throw new InvalidArgumentException("Unsupported service [{$service}].");
            }
            
            return $this->{$serviceMethod}($config);
        }

        if (!method_exists($this, $method = 'create' . ucfirst($transport) . 'Transport')) {
            throw new InvalidArgumentException("Unsupported transport [{$transport}] for service .");
        }
        
        return $this->{$method}($config);
    }

    protected function createTwilioService($config)
    {
        return new TwilioSender($config, $this->events);
    }

    /**
     * @param $config
     * @return LineSender
     */
    protected function createLineService($config)
    {
        return new LineSender($config, $this->events);
    }

    protected function createTwilioTransport($config)
    {
        return new TwilioSender($config, $this->events);
    }

    protected function createTelerivetTransport($config)
    {
        return new TelerivetSender($config, $this->events);
    }

    /**
     * Get the mail connection configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig($service, $name = null)
    {
        if (empty($name)) {
            return $this->config->get("notifications.{$service}.default");
        }

        return $this->config->get("notifications.{$service}.senders.{$name}");
    }
}
