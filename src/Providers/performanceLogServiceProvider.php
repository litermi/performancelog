<?php

namespace Litermi\performanceLog\Providers;
use Illuminate\Support\ServiceProvider;

class performanceLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
         /** @var Router $router */
    $router = $this->app['router'];
    $router->pushMiddlewareToGroup('api', \Litermi\performanceLog\Middleware\PerformanceLog::class);
    }
}
