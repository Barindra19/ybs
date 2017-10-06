<?php

namespace App\Modules\Barcode\Providers;

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
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'barcode');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'barcode');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations', 'barcode');
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
