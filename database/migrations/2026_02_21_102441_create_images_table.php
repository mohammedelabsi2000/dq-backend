<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('images', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship fields
            $table->morphs('imageable'); // يقوم بإنشاء imageable_id و imageable_type

            // Image details
            $table->string('file_name');
            $table->string('file_path');
            $table->string('disk')->default('public'); // storage disk (public, s3, etc)
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // size in bytes

            // Image metadata
            $table->string('image_type')->nullable(); // نوع الصورة (profile, cover, gallery, etc)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_main')->default(false);

            // User tracking
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // Additional notes
            $table->text('notes')->nullable();

            // audit columns (macro)
            $table->auditColumns();

            // Indexes for better performance
            $table->index('image_type');
            $table->index('is_main');
            $table->index('sort_order');
            $table->index('disk');

            // Composite index for common queries
            $table->index(['imageable_id', 'imageable_type', 'is_main']);
            $table->index(['imageable_id', 'imageable_type', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
};
