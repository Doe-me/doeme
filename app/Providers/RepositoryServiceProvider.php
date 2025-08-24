<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Contracts
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\DonationItemRepositoryInterface;
use App\Contracts\Repositories\ChatRepositoryInterface;
use App\Contracts\Repositories\DonationImagesRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;

// Repository Implementations
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\DonationItemRepository;
use App\Repositories\ChatRepository;
use App\Repositories\ReviewRepository;

// Service Contracts
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\CategoryServiceInterface;
use App\Contracts\Services\DonationItemServiceInterface;
use App\Contracts\Services\ChatServiceInterface;
use App\Contracts\Services\ReviewServiceInterface;
use App\Repositories\DonationImagesRepository;
// Service Implementations
use App\Services\AuthService;
use App\Services\CategoryService;
use App\Services\DonationItemService;
use App\Services\ChatService;
use App\Services\ReviewService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Repository Interfaces to Implementations
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(DonationItemRepositoryInterface::class, DonationItemRepository::class);
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(DonationImagesRepositoryInterface::class, DonationImagesRepository::class);

        // Bind Service Interfaces to Implementations
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(DonationItemServiceInterface::class, DonationItemService::class);
        $this->app->bind(ChatServiceInterface::class, ChatService::class);
        $this->app->bind(ReviewServiceInterface::class, ReviewService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

