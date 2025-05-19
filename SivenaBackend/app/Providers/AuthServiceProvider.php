<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies(); // This should be called here

        Passport::tokensExpireIn(now()->addWeeks(1)); // Set token expiration to 1 week
        Passport::refreshTokensExpireIn(now()->addWeeks(2));

        // Admin route gates
        Gate::define('access-admin-panel', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        // Gate for PostController (public creation) - Writers & Admins
        Gate::define('create-posts', function (User $user) {
            return $user->isWriter() || $user->isAdmin();
        });

        // Gate for managing any post in admin (e.g., changing author, deleting)
        Gate::define('manage-any-post-admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-categories', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-tags', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-comments-admin', function(User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-messages', function(User $user) {
            return $user->isAdmin();
        });
    }
}
