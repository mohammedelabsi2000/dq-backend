<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_track_subjects', function (Blueprint $table) {
            $table->string('memorization_direction')
                ->nullable()
                ->after('weight')
                ->comment('اتجاه حفظ المادة في هذه الخطة - فارغ = اتجاه المادة الافتراضي');
            $table->json('juz_directions')
                ->nullable()
                ->after('memorization_direction')
                ->comment('اتجاه خاص لكل جزء {custom_juz_id: direction} - غير المذكور يرث اتجاه المادة');
        });
    }

    public function down(): void
    {
        Schema::table('level_track_subjects', function (Blueprint $table) {
            $table->dropColumn(['memorization_direction', 'juz_directions']);
        });
    }
};
