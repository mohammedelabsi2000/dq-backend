<?php

namespace App\Http\Requests\LevelTrackCourse;

use App\Enums\MemorizationDirection;
use App\Models\LevelTrackSubject;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LevelTrackSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $method = $this->getMethod();
        if ($method === 'POST') {
            // return $this->user()->hasPermissionTo('level_track_subjects.create');
            return $this->user()->can('create', LevelTrackSubject::class);
        } elseif (in_array($method, ['PUT', 'PATCH'])) {
            // return $this->user()->hasPermissionTo('level_track_subjects.update');
            return $this->user()->can('update', $this->route('levelTrackSubject'));
        }
        return true;
    }

    public function rules(): array
    {
        return match ($this->getMethod()) {
            'POST'        => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            default       => [],
        };
    }

    private function storeRules(): array
    {
        return [
            'level_track_id' => ['required', 'exists:level_tracks,id'],
            'subject_id'     => [
                'required',
                'exists:subjects,id',
                Rule::unique('level_track_subjects')->where(
                    fn($q) => $q->where('level_track_id', $this->level_track_id)
                ),
            ],
            'order'       => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
            'weight'      => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            ...$this->directionRules(),
        ];
    }

    private function updateRules(): array
    {
        return [
            // 'level_track_id' => ['sometimes', 'exists:level_tracks,id'],
            'subject_id'     => [
                'sometimes',
                'exists:subjects,id',
                Rule::unique('level_track_subjects')->where(
                    fn($q) => $q->where('level_track_id', $this->level_track_id)
                )->ignore($this->route('levelTrackSubject')->id, 'id'),
            ],
            'order'       => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
            'weight'      => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            ...$this->directionRules(),
        ];
    }

    /**
     * اتجاه حفظ المادة في هذه الخطة، واتجاه خاص لكل جزء {custom_juz_id: direction}
     */
    private function directionRules(): array
    {
        $directions = array_column(MemorizationDirection::cases(), 'value');

        return [
            'memorization_direction' => ['sometimes', 'nullable', 'string', Rule::in($directions)],
            'juz_directions'         => ['sometimes', 'nullable', 'array'],
            'juz_directions.*'       => ['required', 'string', Rule::in($directions)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('juz_directions') || $validator->errors()->hasAny(['subject_id', 'juz_directions'])) {
                return;
            }

            // الأجزاء المخصَّصة يجب أن تكون من أجزاء المادة
            $subject = Subject::find($this->input('subject_id') ?? $this->route('levelTrackSubject')?->subject_id);
            $subjectJuzIds = array_map('intval', json_decode($subject?->custom_juz_id ?? '[]', true) ?: []);

            foreach (array_keys($this->input('juz_directions')) as $juzId) {
                if (!in_array((int) $juzId, $subjectJuzIds, true)) {
                    $validator->errors()->add('juz_directions', "الجزء رقم {$juzId} ليس من أجزاء هذا المساق");
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'level_track_id' => 'مسار المستوى',
            'subject_id'     => 'المساق',
            'order'          => 'الترتيب',
            'is_required'    => 'مطلوب',
            'weight'         => 'الوزن',
            'memorization_direction' => 'اتجاه الحفظ',
            'juz_directions' => 'اتجاهات الأجزاء',
        ];
    }

    public function messages(): array
    {
        return [
            'level_track_id.required' => 'حقل مسار المستوى مطلوب',
            'level_track_id.exists'   => 'مسار المستوى المحدد غير موجود',
            'subject_id.required'     => 'حقل المساق مطلوب',
            'subject_id.exists'       => 'المساق المحدد غير موجود',
            'subject_id.unique'       => 'هذا المساق مضاف مسبقاً لهذا المسار',
            'order.integer'           => 'الترتيب يجب أن يكون رقماً صحيحاً',
            'order.min'               => 'الترتيب يجب أن يكون أكبر من أو يساوي صفر',
            'is_required.boolean'     => 'حقل مطلوب يجب أن يكون صح أو خطأ',
            'weight.numeric'          => 'الوزن يجب أن يكون رقماً',
            'weight.min'              => 'الوزن يجب أن يكون أكبر من أو يساوي صفر',
            'weight.max'              => 'الوزن يجب أن يكون أقل من أو يساوي 100',
            'memorization_direction.in' => 'اتجاه الحفظ يجب أن يكون تصاعدي أو تنازلي',
            'juz_directions.array'    => 'اتجاهات الأجزاء يجب أن تكون بصيغة {رقم الجزء: الاتجاه}',
            'juz_directions.*.in'     => 'اتجاه الجزء يجب أن يكون تصاعدي أو تنازلي',
        ];
    }
}
