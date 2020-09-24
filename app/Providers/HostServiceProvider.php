<?php

namespace App\Providers;

use App\Hosts\HostManager;
use Illuminate\Support\ServiceProvider;

class HostServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('host', function ($app) {
            return new HostManager($app);
        });
    }
}
