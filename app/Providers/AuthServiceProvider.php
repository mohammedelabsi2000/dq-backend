<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Center;
use App\Models\Region;
use App\Models\User;
use App\Models\UserRole;
use App\Policies\BranchPolicy;
use App\Policies\CenterPolicy;
use App\Policies\RegionPolicy;
use App\Policies\UserPolicy;
use App\Policies\UserRolePolicy;
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
        // // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Branch::class => BranchPolicy::class,
        Region::class => RegionPolicy::class,
        Center::class => CenterPolicy::class,
        User::class => UserPolicy::class,
        UserRole::class => UserRolePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
