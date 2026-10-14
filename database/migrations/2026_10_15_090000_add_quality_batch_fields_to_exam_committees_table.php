<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_committees', function (Blueprint $table) {
            $table->string('quality_status')->nullable()->after('status')
                ->comment('مفتاح حالة الدفعة في الجودة، مثل pending_quality_approval (status يحفظ تسميتها العربية)');
            $table->dateTime('exam_datetime')->nullable()->after('proposed_exam_date')->comment('موعد الاختبار المعتمد من الجودة');
            $table->foreignId('teacher_id')->nullable()->after('region_id')->constrained('users')
                ->comment('محفّظ لجنة المساق الواحد (يُرسل للجودة)؛ إن تُرك يؤخذ محفّظ حلقة الطلاب');
        });
    }

    public function down(): void
    {
        Schema::table('exam_committees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn(['quality_status', 'exam_datetime']);
        });
    }
};
