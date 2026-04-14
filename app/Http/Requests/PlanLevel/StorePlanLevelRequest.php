<?php

namespace App\Http\Requests\PlanLevel;

use Illuminate\Validation\Rule;
use App\Http\Requests\DQFormRequest;

class StorePlanLevelRequest extends DQFormRequest
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
            'name' => ['required', 'string', 'max:255'],

            'plan_id' => ['required', 'exists:plans,id'],

            'level_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('plan_levels')
                    ->where(fn($q) => $q->where('plan_id', $this->plan_id))
            ],

            'time_of_level' => ['nullable', 'integer', 'min:1'],

            'time_unit_id' => [
                'required_with:time_of_level',
                'nullable',
                'exists:constants,id'
            ],

            'max_time' => ['nullable', 'integer', 'min:1'],
            'max_time_unit_id' => [
                'required_with:max_time',
                'nullable',
                'exists:constants,id'
            ],

            'min_time' => ['nullable', 'integer', 'min:1'],
            'min_time_unit_id' => [
                'required_with:min_time',
                'nullable',
                'exists:constants,id'
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            // min_time <= time_of_level
            if ($this->min_time && $this->time_of_level && $this->min_time > $this->time_of_level) {
                $validator->errors()->add(
                    'min_time',
                    'Minimum time cannot be greater than time of level.'
                );
            }

            // time_of_level <= max_time
            if ($this->time_of_level && $this->max_time && $this->time_of_level > $this->max_time) {
                $validator->errors()->add(
                    'time_of_level',
                    'Time of level cannot be greater than maximum time.'
                );
            }

            // min_time <= max_time
            if ($this->min_time && $this->max_time && $this->min_time > $this->max_time) {
                $validator->errors()->add(
                    'min_time',
                    'Minimum time cannot be greater than maximum time.'
                );
            }
        });
    }
}