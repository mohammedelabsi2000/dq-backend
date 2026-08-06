<?php

namespace App\Http\Requests\Student;

use App\Enums\MemorizationDirection;
use App\Http\Requests\DQFormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BaseStudentRequest extends DQFormRequest
{

    public function baseRules()
    {
        return [
            'memorized_juz' => 'nullable|string',
            'completed_juz' => 'nullable|string',
            'surah_id' => 'nullable|integer|exists:quran_surahs,id',
            'memorization_direction' => ['nullable', 'string', Rule::in(array_column(MemorizationDirection::cases(), 'value'))],
            'end_aya' => [
                'required_with:surah_id',
                'integer',
                function ($attribute, $value, $fail) {
                    $surahId = request()->input('surah_id');

                    if ($surahId) {
                        $surah = DB::table('quran_surahs')->where('id', $surahId)->first();

                        if ($surah && ($value < 1 || $value > $surah->verses_count)) {
                            $fail('رقم الآية في سورة ' . $surah->name_ar . ' يجب أن يكون من 1 إلى ' . $surah->verses_count);
                        }
                    }
                },
            ],
        ];
    }

    public function baseMessages()
    {
        return [
            'memorized_juz.string' => 'الاختبارات يجب أن تكون نصًا',
            'completed_juz.string' => 'الأجزاء المسرودة المكتملة يجب أن تكون نصًا',
            'surah_id.exists' => 'السورة غير صحيحة',
            'end_aya.integer' => 'رقم الآية يجب أن يكون رقمًا صحيحًا',
            'end_aya.required_with' => 'يجب اختيار رقم الآية عند اختيار سورة',
            'memorization_direction.in' => 'اتجاه الحفظ يجب أن يكون تصاعدي أو تنازلي',
        ];
    }
}