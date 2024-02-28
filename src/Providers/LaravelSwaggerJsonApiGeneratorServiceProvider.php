<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Syn\LaravelSwaggerJsonApiGenerator\Console\OpenApiGenerate;

class LaravelSwaggerJsonApiGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/swagger-jsonapi-generator.php' => config_path('swagger-jsonapi-generator.php'),
            __DIR__.'/../../docs/' => base_path('docs'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'openapi');

        if ($this->app->runningInConsole()) {
            $this->commands([
                OpenApiGenerate::class,
            ]);
        }
    }
}
