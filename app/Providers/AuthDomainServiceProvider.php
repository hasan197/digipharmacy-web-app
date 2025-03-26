<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Infrastructure\Container\AuthContainer;

class AuthDomainServiceProvider extends ServiceProvider
{
    public function register()
    {
        $container = new AuthContainer($this->app);
        $container->register();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
