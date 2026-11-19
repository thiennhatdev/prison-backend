<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use A17\Twill\Facades\TwillPermissions;
use App\Models\Enums\AdminRole;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TwillPermissions::setRoleEnum(AdminRole::class);

        Gate::define('access-commerce', fn ($user) => $user->isPublished() && $user->role === 'NEW');
        Gate::define('access-admin-operations', fn ($user) => $user->isPublished() && $user->role !== 'NEW');
    }
}
