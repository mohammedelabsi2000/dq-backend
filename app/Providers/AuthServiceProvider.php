<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        foreach (config('abilities') as $code => $lable) {
            Gate::define($code, function ($user) use ($code) {
                return $user->hasAbility($code);
            });
        }

        // Gate::define('branches.view', function () {
        //     return true;
        // });

        // Gate::define('branches.create', function () {
        //     return true;
        // });

        // Gate::define('branches.update', function () {
        //     return false;
        // });

        // Gate::define('branches.delete', function () {
        //     return false;
        // });
    }
}
