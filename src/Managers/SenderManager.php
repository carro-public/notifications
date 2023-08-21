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
    
    protected $logger;

    public function __construct($app)
    {
        $this->config = $app['config'];
        $this->events = $app['events'];
        $this->logger = $app['log'];
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
     * @param $service
     * @param $name
     * @param $instance
     * @return void
     */
    public function registerSender($service, $name, $instance)
    {
        $this->senders[$service][$name] = $instance instanceof \Closure ? $instance() : $instance;
    }

    /**
     * Register customer Sender for a service's sender
     * @param $service
     * @param $name
     * @param $instance
     * @return void
     */
    public static function extendSender($service, $name, $instance)
    {
        /** @var SenderManager $manager */
        $manager = app(SenderManager::class);
        $manager->registerSender($service, $name, $instance);
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
        return new TwilioSender($config, $this->events, $this->logger);
    }

    /**
     * @param $config
     * @return LineSender
     */
    protected function createLineService($config)
    {
        return new LineSender($config, $this->events, $this->logger);
    }

    protected function createTwilioTransport($config)
    {
        return new TwilioSender($config, $this->events, $this->logger);
    }

    protected function createTelerivetTransport($config)
    {
        return new TelerivetSender($config, $this->events, $this->logger);
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
