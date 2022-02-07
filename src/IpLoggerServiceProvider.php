<?php

namespace AmirHossein5\LaravelIpLogger;

use Illuminate\Support\ServiceProvider;

class IpLoggerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('ipLogger', function ($app) {
            return new IpLogger();
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/ipLogger.php', 'ipLogger');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ipLogger.php' => config_path('ipLogger.php'),
            ], 'ipLogger');
        }
    }
}
