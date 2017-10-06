<?php

namespace App\Modules\Cashbook\Providers;

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
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'cashbook');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'cashbook');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations', 'cashbook');
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
