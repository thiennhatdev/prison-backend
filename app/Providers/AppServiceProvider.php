<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use A17\Twill\Facades\TwillPermissions;
use App\Models\Enums\AdminRole;
use A17\Twill\Models\User as AdminUser;
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

        // Twill's `role` is a string with legacy roles and a Role model when
        // permissions management is enabled. Deny Twill permissions for NEW.
        Gate::before(fn ($user, string $ability) => $user instanceof AdminUser
            && AdminRole::isNewUser($user) && $ability !== 'access-commerce'
            ? false
            : null);

        Gate::define('access-commerce', fn ($user) => $user instanceof AdminUser
            && $user->isPublished() && AdminRole::isNewUser($user));
        Gate::define('access-admin-operations', fn ($user) => $user instanceof AdminUser
            && $user->isPublished() && ! AdminRole::isNewUser($user));
    }
}
