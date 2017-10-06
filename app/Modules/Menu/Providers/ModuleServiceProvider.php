<?php

namespace App\Modules\Menu\Providers;

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
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'menu');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'menu');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations', 'menu');
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
