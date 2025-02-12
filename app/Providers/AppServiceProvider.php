<?php

namespace App\Providers;

use App\Password\BrokerManager as PasswordBrokerManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend('auth.password', function () {
            return new PasswordBrokerManager($this->app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        // Model::shouldBeStrict(
        //     ! $this->app->environment('production'),
        // );
    }
}
