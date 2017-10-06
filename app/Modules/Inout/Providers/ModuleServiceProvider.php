<?php

namespace App\Modules\Inout\Providers;

use Caffeinated\Modules\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'inout');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'inout');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations', 'inout');
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
