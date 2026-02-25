<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Observers\AuditObserver;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

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
        Paginator::useBootstrap();

        \Maatwebsite\Excel\Imports\HeadingRowFormatter::default(
            \Maatwebsite\Excel\Imports\HeadingRowFormatter::FORMATTER_NONE
        );



        /*
        |--------------------------------------------------------------------------
        | دالة لاستخدامها داخل migration تقوم بإضافة هذه الأعمدة
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | جلب كل ملفات الموديلات داخل App\Models
        | تسجيل AuditObserver لهذه الموديلات
        |--------------------------------------------------------------------------
        */

        $modelPath = app_path('Models');
        foreach (File::allFiles($modelPath) as $file) {
            $class = 'App\\Models\\' . Str::replaceLast('.php', '', $file->getFilename());
            if (class_exists($class) && property_exists($class, 'usesAudit') && $class::$usesAudit) {
                $class::observe(AuditObserver::class);
            }
        }
    }
}
