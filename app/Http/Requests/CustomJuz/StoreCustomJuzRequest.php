<?php

namespace App\Http\Requests\CustomJuz;

use Illuminate\Support\Facades\DB;

class StoreCustomJuzRequest extends BaseCustomJuzRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'sort_order' => 'required|integer',
            'name' => 'required|string|unique:custom_juz,name',
            'start_surah_id' => 'required|integer|exists:quran_surahs,id',
            'start_aya' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $surah = DB::table('quran_surahs')
                        ->where('id', $this->input('start_surah_id'))
                        ->first();
                    if (!$surah || $value < 1 || $value > $surah->verses_count) {
                        $fail('رقم الآية في سورة ' . $surah->name_ar . ' يجب أن يكون من 1 إلى ' . $surah->verses_count);
                    }
                },
            ],
            'end_surah_id' => 'required|integer|exists:quran_surahs,id',
            'end_aya' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $surah = DB::table('quran_surahs')
                        ->where('id', $this->input('end_surah_id'))
                        ->first();
                    if (!$surah || $value < 1 || $value > $surah->verses_count) {
                        $fail('رقم الآية في سورة ' . $surah->name_ar . ' يجب أن يكون من 1 إلى ' . $surah->verses_count);
                    }
                },
            ],
        ];
    }


    public function messages()
    {
        return [
            'sort_order.required' => 'ترتيب الجزء مطلوب',
            'sort_order.integer' => 'ترتيب الجزء يجب أن يكون رقمًا صحيحًا',
            'name.required' => 'اسم الجزء مطلوب',
            'name.unique' => 'اسم الجزء مسجل مسبقا',
            'start_surah_id.required' => 'رقم السورة التي يبدأ بها الجزء مطلوب',
            'start_surah_id.exists' => 'رقم السورة التي يبدأ بها الجزء غير صحيح',
            'start_aya.required' => 'رقم الآية التي يبدأ بها الجزء مطلوب',
            'end_surah_id.required' => 'رقم السورة التي ينتهي بها الجزء مطلوب',
            'end_surah_id.exists' => 'رقم السورة التي ينتهي بها الجزء غير صحيح',
            'end_aya.required' => 'رقم الآية التي ينتهي بها الجزء مطلوب',
            'end_aya.integer' => 'رقم الآية التي ينتهي بها الجزء يجب أن يكون رقمًا صحيحًا',
        ];
    }
}
