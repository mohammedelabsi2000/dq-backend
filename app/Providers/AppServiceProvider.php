<?php

namespace App\Providers;

use App\Models\{
    Branch,
    Center,
    DailyAchievement,
    Halaqa,
    Mosque,
    Region,
    Student,
    User,
};
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Observers\AuditObserver;
use App\Observers\DailyAchievementObserver;
use App\Support\CurrentUserContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\Relation;


class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CurrentUserContext::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePagination();
        $this->configureSchema();
        $this->configureExcel();
        $this->registerAuditMacro();
        $this->registerAuditObservers();
        $this->configureMorphMap();
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    protected function configurePagination(): void
    {
        Paginator::useBootstrapFive();
    }

    /*
    |--------------------------------------------------------------------------
    | Schema Defaults
    |--------------------------------------------------------------------------
    */
    protected function configureSchema(): void
    {
        Schema::defaultStringLength(191);
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Configuration
    |--------------------------------------------------------------------------
    */
    protected function configureExcel(): void
    {
        \Maatwebsite\Excel\Imports\HeadingRowFormatter::default(
            \Maatwebsite\Excel\Imports\HeadingRowFormatter::FORMATTER_NONE
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Audit Columns Macro
    |--------------------------------------------------------------------------
    */
    protected function registerAuditMacro(): void
    {
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

    /*
    |--------------------------------------------------------------------------
    | Register Audit Observers Automatically
    |--------------------------------------------------------------------------
    */
    protected function registerAuditObservers(): void
    {
        $modelPath = app_path('Models');

        foreach (File::allFiles($modelPath) as $file) {
            $class = 'App\\Models\\' . Str::replaceLast('.php', '', $file->getFilename());

            if (
                class_exists($class) &&
                property_exists($class, 'usesAudit') &&
                    $class::$usesAudit
            ) {
                $class::observe(AuditObserver::class);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Morph Map Configuration
    |--------------------------------------------------------------------------
    */
    protected function configureMorphMap(): void
    {
        Relation::morphMap([
            'user' => User::class,
            'branch' => Branch::class,
            'region' => Region::class,
            'center' => Center::class,
            'mosque' => Mosque::class,
            'halaqa' => Halaqa::class,
            'student' => Student::class,
        ]);
    }
}
