<?php

namespace App\Infrastructure\Auth\Providers;

use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Infrastructure\Auth\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
}
