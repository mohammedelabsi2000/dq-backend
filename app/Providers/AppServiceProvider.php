<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use App\Observers\AuditObserver;
use Illuminate\Support\Facades\Schema;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Model;

use Database\BlueprintMacros\AuditColumns;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\Paginator;


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

        Paginator::useBootstrapFive();


        Schema::defaultStringLength(191);

        // Model::observe(AuditObserver::class);
        // AuditColumns::register();

        Blueprint::macro('auditColumns', function () {
            /** @var Blueprint $this */

            $this->timestamps();

            $this->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $this->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $this->softDeletes();

            $this->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }
}
