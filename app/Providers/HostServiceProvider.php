<?php

namespace App\Providers;

use App\Hosts\Contracts\DriverContract;
use App\Hosts\Contracts\SecurityContract;
use App\Hosts\HostManager;
use Illuminate\Support\ServiceProvider;

class HostServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(HostManager::class, function ($app) {
            return new HostManager($app);
        });

        $this->app->alias(
            HostManager::class, DriverContract::class
        );

        $this->app->alias(
            HostManager::class, SecurityContract::class
        );
    }
}
