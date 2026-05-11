<?php
namespace App\Imports\Student;

use App\Imports\Student\Concerns\HasColumnMap;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ValidateStudentsImport implements WithHeadingRow, ToCollection
{
    use HasColumnMap;
    protected Request $request;
    public array $errors = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            $validator = Validator::make($row->toArray(), [
                $this->getKey('branch') => 'required',
                $this->getKey('region') => 'required',
                // $this->getKey('mosque') => 'required',
                $this->getKey('student_identity') => 'required|numeric|digits:9',
                $this->getKey('teacher_identity') => 'required|numeric|digits:9',

                $this->getKey('recitation_from') => 'nullable|numeric|between:1,30',
                $this->getKey('recitation_to') => 'nullable|numeric|between:1,30',

                $this->getKey('hifz_from') => 'nullable|numeric|between:1,30',
                $this->getKey('hifz_to') => 'nullable|numeric|between:1,30',
            ], [
                $this->getKey('branch') . '.required' => 'اسم الفرع مطلوب',
                $this->getKey('region') . '.required' => 'اسم المحلية مطلوب',
                // student_identity
                $this->getKey('student_identity') . '.required' => 'رقم هوية الطالب مطلوب',
                $this->getKey('student_identity') . '.numeric' => 'رقم هوية الطالب يجب أن يكون رقمًا',
                $this->getKey('student_identity') . '.digits' => 'رقم هوية الطالب يجب أن يتكون من 9 أرقام',
                // teacher_identity
                $this->getKey('teacher_identity') . '.required' => 'رقم هوية المعلم مطلوب',
                $this->getKey('teacher_identity') . '.numeric' => 'رقم هوية المعلم يجب أن يكون رقمًا',
                $this->getKey('teacher_identity') . '.digits' => 'رقم هوية المعلم يجب أن يتكون من 9 أرقام',

                $this->getKey('recitation_from') . '.numeric' => 'السرد من يجب أن يكون رقمًا',
                $this->getKey('recitation_from') . '.between' => 'السرد من يجب أن يكون بين 1 و 30',
                $this->getKey('recitation_to') . '.numeric' => 'السرد إلى يجب أن يكون رقمًا',
                $this->getKey('recitation_to') . '.between' => 'السرد إلى يجب أن يكون بين 1 و 30',

                $this->getKey('hifz_from') . '.numeric' => 'الحفظ من يجب أن يكون رقمًا',
                $this->getKey('hifz_from') . '.between' => 'الحفظ من يجب أن يكون بين 1 و 30',
                $this->getKey('hifz_to') . '.numeric' => 'الحفظ إلى يجب أن يكون رقمًا',
                $this->getKey('hifz_to') . '.between' => 'الحفظ إلى يجب أن يكون بين 1 و 30',
            ]);

            if ($validator->fails()) {
                $this->errors = [
                    // 'row' => $index + 2,
                    'message' => 'خطأ في بيانات الطالب: ' . ($row[$this->getKey('name')] ?? 'غير معروف') . ' صف رقم ' . ($index + 2),
                    'errors' => $validator->errors()->all(),
                    // 'values' => $row,
                ];
                return;
            }
        }
    }
}
