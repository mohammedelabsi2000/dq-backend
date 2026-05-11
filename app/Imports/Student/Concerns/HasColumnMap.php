<?php

namespace App\Imports\Student\Concerns;

trait HasColumnMap
{
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
        'السرد من' => 'recitation_from',
        'السرد إلى' => 'recitation_to',
        'الحفظ من' => 'hifz_from',
        'الحفظ إلى' => 'hifz_to',
    ];

    /**
     * Get the key for a given value in the map.
     * @param string $value
     * @return bool|int|string
     */
    private function getKey(string $value)
    {
        return array_search($value, $this->map);
    }
}