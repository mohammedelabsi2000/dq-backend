<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Student::query()
            ->with([
                'mosque.region',
                'mosque',
            ]);
    }

    public function headings(): array
    {
        return [
            'رقم الهوية',
            'اسم الطالب',
            'تاريخ الميلاد',
            'الجنس',
            'رقم التواصل',
            'رقم الواتساب',
            'المحلية',
            'المسجد',
            'الحلقة',
            'الأجزاء التي تم حفظها',
            'الأجزاء التي تم سردها',
            'آخر سورة من الحفظ',
            'آخر آية من الحفظ',
            'اتجاه الحفظ',
            'الحالة الاجتماعية',
            'الحالة المادية',
            'ولي الأمر',
            'رقم هوية ولي الأمر',
        ];
    }

    public function map($student): array
    {
        return [
            $student->identity,
            $student->full_name,
            $student->dob,
            $student->gender->label(),
            $student->phone,
            $student->whatsapp,
            $student->mosque?->region?->name,
            $student->mosque?->name,
            $student->halaqas->last()?->name ?? null,
            $student->memorized_juz,
            $student->completed_juz,
            $student->surah?->name_ar,
            $student->end_aya,
            $student->memorization_direction?->label(),
            $student->maritalStatus?->name,
            $student->moneyStatus?->name,
            $student->guardianType?->name,
            $student->guardian_id,
        ];
    }
}