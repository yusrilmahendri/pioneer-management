<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Contracts and Implementations
use App\Repository\Contracts\UserRepositoryInterface;
use App\Repository\UserRepository;
use App\Repository\Contracts\ProductRepositoryInterface;
use App\Repository\ProductRepository;
use App\Repository\BusinessRepositoryInterface;
use App\Repository\BusinessRepository;

// Usecase Contracts and Implementations
use App\Usecase\Contracts\UserUsecaseInterface;
use App\Usecase\UserUsecase;
use App\Usecase\Contracts\ProductUsecaseInterface;
use App\Usecase\ProductUsecase;
use App\Usecase\BusinessUsecase;
use App\Usecase\DashboardUsecase;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(BusinessRepositoryInterface::class, BusinessRepository::class);
        
        // Usecase bindings
        $this->app->bind(UserUsecaseInterface::class, UserUsecase::class);
        $this->app->bind(ProductUsecaseInterface::class, ProductUsecase::class);
        $this->app->singleton(BusinessUsecase::class);
        $this->app->singleton(DashboardUsecase::class);

        // Add more repository and usecase bindings here as you create them
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}