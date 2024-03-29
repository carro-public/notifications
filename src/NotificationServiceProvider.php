<?php

namespace CarroPublic\Notifications;

use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use CarroPublic\Notifications\Channels\SMSChannel;
use CarroPublic\Notifications\Channels\LineChannel;
use CarroPublic\Notifications\Managers\SenderManager;
use CarroPublic\Notifications\Channels\SMS2WayChannel;
use CarroPublic\Notifications\Channels\WhatsAppChannel;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot() {
        $this->loadViewsFrom(__DIR__.'/../views', 'notifications');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            // Publishing the configuration file.
            $this->publishes([
                __DIR__.'/../config/notifications.php' => config_path('notifications.php'),
            ], 'config');
        }
    }
    
    public function register()
    {
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
