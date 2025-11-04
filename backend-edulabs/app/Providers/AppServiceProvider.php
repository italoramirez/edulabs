<?php

namespace App\Providers;

use App\Repository\Auth\AuthRepository;
use App\Repository\Auth\Impl\AuthRepositoryImpl;
use App\Repository\Setting\Impl\SettingsRepositoryImpl;
use App\Repository\Setting\SettingsRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\Impl\AuthServiceImpl;
use App\Services\Settings\Impl\SettingsServiceImpl;
use App\Services\Settings\SettingsService;
use App\Services\Settings\StorageLimitService;
use App\Services\Settings\StorageLimitServiceImpl;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*Services*/

        $this->app->bind(
            SettingsService::class,
            SettingsServiceImpl::class
        );

        $this->app->bind(
            AuthService::class,
            AuthServiceImpl::class
        );

        $this->app->bind(
            StorageLimitService::class,
            StorageLimitServiceImpl::class
        );


        /*Repositories*/

        $this->app->bind(
            SettingsRepository::class,
            SettingsRepositoryImpl::class
        );

        $this->app->bind(
            AuthRepository::class,
            AuthRepositoryImpl::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
