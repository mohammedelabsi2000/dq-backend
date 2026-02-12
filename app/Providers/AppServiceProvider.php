<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use App\Observers\AuditObserver;
use Illuminate\Support\Facades\Schema;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Model;

use Database\BlueprintMacros\AuditColumns;
use Illuminate\Database\Schema\Blueprint;

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

        Schema::defaultStringLength(191);

        // Model::observe(AuditObserver::class);
        // AuditColumns::register();
    }
}
