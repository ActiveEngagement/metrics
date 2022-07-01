<?php

namespace Actengage\Metrics;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // $this->mergeConfigFrom(
        //     __DIR__.'/../config/media.php', 'media'
        // );

        // $this->app->bind(MetricFactory::class, function($app) {
        //     return new MetricFactory();
        // });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // $this->publishes([
        //     __DIR__.'/../config/media.php' => config_path('media.php')
        // ], 'media-config');

        DateRange::boot($this->app);
    }
}