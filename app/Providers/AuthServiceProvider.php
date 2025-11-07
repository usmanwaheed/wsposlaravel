<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-reports', function ($user) {
            return in_array($user->role, ['admin', 'cashier']);
        });

        Gate::define('manage-products', function ($user) {
            return $user->role === 'admin';
        });
    }
}
