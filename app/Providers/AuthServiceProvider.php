<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Center;
use App\Models\Region;
use App\Policies\BranchPolicy;
use App\Policies\CenterPolicy;
use App\Policies\RegionPolicy;
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
        // Branch::class => BranchPolicy::class,
        // Region::class => RegionPolicy::class,
        // Center::class => CenterPolicy::class,
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
