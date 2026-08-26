<?php

namespace App\Http\Requests\HalaqaSponsorship;

use App\Models\Halaqa;
use App\Http\Requests\DQFormRequest;

class StopHalaqaSponsorshipRequest extends DQFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var Halaqa $halaqa */
        $halaqa = $this->route('halaqa');

        return $this->user()->can('update', $halaqa);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'to_date' => 'nullable|date',
            'stop_reason' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $halaqaSponsorship = $this->route('halaqaSponsorship');

            if ($this->filled('to_date') && $halaqaSponsorship && $this->input('to_date') < $halaqaSponsorship->from_date->format('Y-m-d')) {
                $validator->errors()->add(
                    'to_date',
                    'تاريخ الإيقاف يجب أن يكون بعد أو يساوي تاريخ بدء الكفالة'
                );
            }
        });
    }
}
