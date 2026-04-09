<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\DQFormRequest;
use Illuminate\Support\Facades\DB;

class BaseStudentRequest extends DQFormRequest
{

    public function baseRules()
    {
        return [
            'memorized_juz_id' => 'nullable|integer|exists:custom_juz,id',
            'completed_juz_id' => 'nullable|integer|exists:custom_juz,id',
            'surah_id' => 'nullable|integer|exists:quran_surahs,id',
            'end_aya' => [
                'required_with:surah_id',
                'integer',
                function ($attribute, $value, $fail) {
                    $surahId = request()->input('surah_id');

                    if ($surahId) {
                        $surah = DB::table('quran_surahs')->where('id', $surahId)->first();

                        if ($surah && ($value < 1 || $value > $surah->verses_count)) {
                            $fail("رقم الآية يجب أن يكون بين 1 و {$surah->verses_count}");
                        }
                    }
                },
            ],
        ];
    }

    public function baseMessages()
    {
        return [
            'memorized_juz_id.exists' => 'جزء الحفظ غير صحيح',
            'completed_juz_id.exists' => 'جزء السرد غير صحيح',
            'surah_id.exists' => 'السورة غير صحيحة',
            'end_aya.integer' => 'رقم الآية يجب أن يكون رقمًا صحيحًا',
            'end_aya.required_with' => 'يجب اختيار رقم الآية عند اختيار سورة',
        ];
    }
}
