<?php

namespace App\Modules\Submenu\Providers;

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
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'submenu');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'submenu');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations', 'submenu');
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
