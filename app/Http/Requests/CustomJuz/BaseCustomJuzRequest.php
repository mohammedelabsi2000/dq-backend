<?php

namespace App\Http\Requests\CustomJuz;

use App\Http\Requests\DQFormRequest;

class BaseCustomJuzRequest extends DQFormRequest
{
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $startSurahId = $this->input('start_surah_id');
            $endSurahId = $this->input('end_surah_id');
            $startAya = $this->input('start_aya');
            $endAya = $this->input('end_aya');

            // تحقق أن سورة البداية <= سورة النهاية
            if ($startSurahId > $endSurahId) {
                $validator->errors()->add(
                    'start_surah_id',
                    'سورة البداية يجب أن تكون قبل أو تساوي سورة النهاية'
                );
            }

            // إذا نفس السورة → تحقق من ترتيب الآيات
            if ($startSurahId == $endSurahId && $startAya > $endAya) {
                $validator->errors()->add(
                    'start_aya',
                    'يجب أن تكون آية البداية أقل أو تساوي آية النهاية'
                );
            }
        });
    }
}
