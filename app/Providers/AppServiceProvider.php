<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Model;
use Database\BlueprintMacros\AuditColumns;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        // Blueprint::macro('addAuditColumns', function () {
        //     // $this->unsignedBigInteger('created_by')->nullable();
        //     // $this->unsignedBigInteger('updated_by')->nullable();
        // });

        // AuditColumns::register();

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Model::observe(AuditObserver::class);
        AuditColumns::register();
    }
}
