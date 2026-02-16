<?php

namespace Database\BlueprintMacros;

use Illuminate\Database\Schema\Blueprint;

class AuditColumns
{
    // public function addAuditColumns(Blueprint $table)
    // {
    //     $table->timestamps();

    //     $table->foreignId('created_by')
    //         ->nullable()
    //         ->constrained('users')
    //         ->nullOnDelete();

    //     $table->foreignId('updated_by')
    //         ->nullable()
    //         ->constrained('users')
    //         ->nullOnDelete();

    //     $table->softDeletes();

    //     $table->foreignId('deleted_by')
    //         ->nullable()
    //         ->constrained('users')
    //         ->nullOnDelete();
    // }

    public static function register(): void
    {
        Blueprint::macro('auditColumns', function () {
            /** @var Blueprint $this */
            // $this->unsignedBigInteger('created_by')->nullable();
            // $this->unsignedBigInteger('updated_by')->nullable();

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
