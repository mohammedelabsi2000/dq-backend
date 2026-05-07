<?php
namespace App\Imports\Student;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ValidateStudentsImport implements WithHeadingRow, ToCollection
{
    protected Request $request;
    public array $errors = [];
    private array $map = [
        'الفرع' => 'branch',
        'المحلية' => 'region',
        'المسجد/ المركز' => 'mosque',
        'اسم الطالب رباعيًا' => 'name',
        'رقم هوية الطالب' => 'student_identity',
        'تاريخ الميلاد' => 'dob',
        'نوع الكفالة' => 'sponsor_type',
        'جهة الكفالة' => 'sponsor_entity',
        'اسم المعلم رباعيًا' => 'teacher_name',
        'رقم هوية المعلم' => 'teacher_identity',
        'عدد أجزاء الحفظ' => 'hifz_parts',
        // 'آخر إنجاز للحفظ',
        'السورة' => 'surah',
        'الاية' => 'ayah',
        'عدد أجزاء السرد' => 'recitation_parts',
    ];

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
            ]);

            if ($validator->fails()) {
                // $this->errors[] = $validator->errors()->all();
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

    private function getKey(string $value)
    {
        return array_search($value, $this->map);
    }
}
