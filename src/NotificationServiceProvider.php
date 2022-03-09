<?php

namespace CarroPublic\Notifications;

use Illuminate\Support\ServiceProvider;
use CarroPublic\Notifications\Managers\SenderManager;

class NotificationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/notifications.php', 'notifications');
        
        // Register the service the package provides.
        $this->app->singleton(SenderManager::class, function ($app) {
            return new SenderManager($app);
        });
    }
}
