<?php

namespace App\Http\Requests\PlanLevel;

use Illuminate\Validation\Rule;
use App\Http\Requests\DQFormRequest;

class UpdatePlanLevelRequest extends DQFormRequest
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

        $levelId = $this->route('plan_level')?->id ?? $this->route('plan_level');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'plan_id' => ['sometimes', 'required', 'exists:plans,id'],

            'level_order' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                Rule::unique('plan_levels')
                    ->ignore($levelId)
                    ->where(
                        fn($q) =>
                        $q->where('plan_id', $this->plan_id ?? $this->plan_level->plan_id)
                    ),
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

            $time = $this->time_of_level ?? $this->plan_level->time_of_level;
            $min = $this->min_time ?? $this->plan_level->min_time;
            $max = $this->max_time ?? $this->plan_level->max_time;

            if ($min && $time && $min > $time) {
                $validator->errors()->add(
                    'min_time',
                    'Minimum time cannot be greater than time of level.'
                );
            }

            if ($time && $max && $time > $max) {
                $validator->errors()->add(
                    'time_of_level',
                    'Time of level cannot be greater than maximum time.'
                );
            }

            if ($min && $max && $min > $max) {
                $validator->errors()->add(
                    'min_time',
                    'Minimum time cannot be greater than maximum time.'
                );
            }
        });
    }
}
