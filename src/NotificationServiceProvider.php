<?php

namespace CarroPublic\Notifications;

use Illuminate\Support\ServiceProvider;
use CarroPublic\Notifications\Managers\SenderManager;

class NotificationServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the service the package provides.
        $this->app->singleton(SenderManager::class, function ($app) {
            return new SenderManager($app);
        });
    }
}
