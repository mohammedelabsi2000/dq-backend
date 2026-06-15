<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LevelTrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level_id' => ['required', 'exists:levels,id'],
            'track_id' => ['required', 'exists:tracks,id'],
            'weight'   => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'level_id' => 'المستوى',
            'track_id' => 'المسار',
            'weight'   => 'الوزن / النسبة المئوية',
        ];
    }
}
