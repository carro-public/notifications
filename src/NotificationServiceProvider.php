<?php

namespace CarroPublic\Notifications;

use Illuminate\Support\Facades\Bus;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Foundation\Application;
use CarroPublic\Notifications\Channels\SMSChannel;
use CarroPublic\Notifications\Channels\LineChannel;
use CarroPublic\Notifications\Managers\SenderManager;
use CarroPublic\Notifications\Channels\SMS2WayChannel;
use CarroPublic\Notifications\Channels\WhatsAppChannel;
use CarroPublic\Notifications\Models\NotificationModelProvider;
use CarroPublic\Notifications\Subscribers\NotificationEventSubscribers;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot() {
        $this->loadViewsFrom(__DIR__.'/../views', 'notifications');
        
        /** @var Application $app */
        $app = Container::getInstance();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            // Publishing the configuration file.
            $this->publishes([
                __DIR__.'/../config/notifications.php' => $app->configPath('notifications.php'),
            ], 'config');
        }
        
        $this->mergeConfigFrom(__DIR__.'/../config/notifications.php', 'notifications');

        Event::subscribe(NotificationEventSubscribers::class);
    }
    
    public function register()
    {
        $this->app->singleton(NotificationModelProvider::class, function ($app) {
            $providerClass = $app->get('config')->get('notifications.model.provider');
            return new $providerClass;
        });
        
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('sms', function ($app) {
                return $app->make(SMSChannel::class);
            });
            $service->extend('sms2way', function ($app) {
                return $app->make(SMS2WayChannel::class);
            });
            $service->extend('whatsapp', function ($app) {
                return $app->make(WhatsAppChannel::class);
            });
            $service->extend('line', function ($app) {
                return $app->make(LineChannel::class);
            });
        });
        
        // Register the service the package provides.
        $this->app->singleton(SenderManager::class, function ($app) {
            return new SenderManager($app);
        });
    }
}
