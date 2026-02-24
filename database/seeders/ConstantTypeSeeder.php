<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConstantType;
use App\Models\Constant;

class ConstantTypeSeeder extends Seeder
{
    public function run(): void
    {
        $constantTypes = [
            [
                'name' => 'marital_status',
                'description' => 'الحالة الاجتماعية',
                'notes' => 'أنواع الحالة الاجتماعية للمستخدمين',
                'constants' => [
                    ['name' => 'أعزب', 'notes' => 'غير متزوج', 'is_active' => true],
                    ['name' => 'متزوج', 'notes' => null, 'is_active' => true],
                    ['name' => 'مطلق', 'notes' => null, 'is_active' => true],
                    ['name' => 'أرمل', 'notes' => null, 'is_active' => true],
                ]
            ],
            [
                'name' => 'prefix_name',
                'description' => 'ألقاب قبل الاسم',
                'notes' => 'الألقاب الشرفية والدينية',
                'constants' => [
                    ['name' => 'الشيخ', 'notes' => null, 'is_active' => true],
                    ['name' => 'الدكتور', 'notes' => null, 'is_active' => true],
                    ['name' => 'الأستاذ', 'notes' => null, 'is_active' => true],
                    ['name' => 'الحاج', 'notes' => null, 'is_active' => true],
                ]
            ],
            [
                'name' => 'plan_type',
                'description' => 'أنواع الخطط',
                'notes' => 'تصنيفات الخطط التعليمية',
                'constants' => [
                    ['name' => 'تحفيظ القرآن', 'notes' => 'خطة لتحفيظ القرآن الكريم', 'is_active' => true],
                    ['name' => 'تجويد', 'notes' => 'خطة لتعليم التجويد', 'is_active' => true],
                    ['name' => 'علوم شرعية', 'notes' => 'خطة للعلوم الشرعية', 'is_active' => true],
                    ['name' => 'لغة عربية', 'notes' => 'خطة لتعليم اللغة العربية', 'is_active' => true],
                ]
            ],
            [
                'name' => 'target_group',
                'description' => 'الفئات المستهدفة',
                'notes' => 'الفئات العمرية والمستهدفة',
                'constants' => [
                    ['name' => 'أطفال', 'notes' => 'من 6-12 سنة', 'is_active' => true],
                    ['name' => 'ناشئة', 'notes' => 'من 13-18 سنة', 'is_active' => true],
                    ['name' => 'شباب', 'notes' => 'من 19-35 سنة', 'is_active' => true],
                    ['name' => 'كبار', 'notes' => 'فوق 35 سنة', 'is_active' => true],
                ]
            ],
            [
                'name' => 'time_unit',
                'description' => 'وحدات الوقت',
                'notes' => 'وحدات قياس الوقت',
                'constants' => [
                    ['name' => 'يوم', 'notes' => null, 'is_active' => true],
                    ['name' => 'أسبوع', 'notes' => null, 'is_active' => true],
                    ['name' => 'شهر', 'notes' => null, 'is_active' => true],
                    ['name' => 'سنة', 'notes' => null, 'is_active' => true],
                ]
            ],
            [
                'name' => 'academic_degree',
                'description' => 'الدرجات العلمية',
                'notes' => 'المؤهلات الأكاديمية',
                'constants' => [
                    ['name' => 'ثانوية عامة', 'notes' => null, 'is_active' => true],
                    ['name' => 'دبلوم', 'notes' => 'سنتان بعد الثانوية', 'is_active' => true],
                    ['name' => 'بكالوريوس', 'notes' => 'أربع سنوات', 'is_active' => true],
                    ['name' => 'ماجستير', 'notes' => 'دراسات عليا', 'is_active' => true],
                    ['name' => 'دكتوراه', 'notes' => 'دراسات عليا متقدمة', 'is_active' => true],
                ]
            ],
            [
                'name' => 'major',
                'description' => 'التخصصات',
                'notes' => 'التخصصات العلمية',
                'constants' => [
                    ['name' => 'هندسة', 'notes' => null, 'is_active' => true],
                    ['name' => 'طب', 'notes' => null, 'is_active' => true],
                    ['name' => 'صيدلة', 'notes' => null, 'is_active' => true],
                    ['name' => 'شريعة', 'notes' => null, 'is_active' => true],
                    ['name' => 'لغة عربية', 'notes' => null, 'is_active' => true],
                    ['name' => 'تربية', 'notes' => null, 'is_active' => true],
                ]
            ],
            [
                'name' => 'course_type',
                'description' => 'أنواع الدورات',
                'notes' => 'تصنيفات الدورات الشخصية',
                'constants' => [
                    ['name' => 'تطوير ذاتي', 'notes' => null, 'is_active' => true],
                    ['name' => 'مهنية', 'notes' => null, 'is_active' => true],
                    ['name' => 'حاسوب', 'notes' => null, 'is_active' => true],
                    ['name' => 'لغات', 'notes' => null, 'is_active' => true],
                    ['name' => 'دينية', 'notes' => null, 'is_active' => true],
                ]
            ],
            [
                'name' => 'guardian_type',
                'description' => 'صلة القرابة',
                'notes' => '',
                'constants' => [
                    // ['name' => 'بنفسه', 'notes' => null, 'is_active' => true],
                    // ['name' => 'أب', 'notes' => null, 'is_active' => true],
                    // ['name' => 'جد', 'notes' => null, 'is_active' => true],
                    // ['name' => 'غم', 'notes' => null, 'is_active' => true],
                    // ['name' => 'خال', 'notes' => null, 'is_active' => true],
                ]
            ],
            [
                'name' => 'money_status',
                'description' => 'الحالة المادية',
                'notes' => '',
                'constants' => [
                    ['name' => 'فقير', 'notes' => null, 'is_active' => true],
                    ['name' => 'محتاج', 'notes' => null, 'is_active' => true],
                    ['name' => 'متوسط', 'notes' => null, 'is_active' => true],
                    ['name' => 'ميسور', 'notes' => null, 'is_active' => true],
                ]
            ],
            [
                'name' => 'name_prefix',
                'description' => 'بادئة الاسم',
                'notes' => '',
                'constants' => [
                    ['name' => 'السيد', 'notes' => null, 'is_active' => true],
                    ['name' => 'السيدة', 'notes' => null, 'is_active' => true],
                    ['name' => 'الآنسة', 'notes' => null, 'is_active' => true],
                    ['name' => 'الدكتور', 'notes' => null, 'is_active' => true],
                    ['name' => 'المهندس', 'notes' => null, 'is_active' => true],
                ]
            ],
            [
                'name' => 'enrollment_status',
                'description' => 'حالة تسجيل الطالب في الحلقة',
                'notes' => '',
                'constants' => [
                    ['name' => 'منتظم', 'notes' => null, 'is_active' => true],
                    ['name' => 'مفصول', 'notes' => null, 'is_active' => true],
                    ['name' => 'منقطع', 'notes' => null, 'is_active' => true],
                    ['name' => 'ثانوية', 'notes' => null, 'is_active' => true],
                ]
            ],
        ];

        foreach ($constantTypes as $typeData) {
            $constants = $typeData['constants'];
            unset($typeData['constants']);
            
            $constantType = ConstantType::create([
                ...$typeData,
                // 'created_by' => 1,
                // 'updated_by' => 1,
            ]);
            
            foreach ($constants as $constant) {
                Constant::create([
                    ...$constant,
                    'constant_type_id' => $constantType->id,
                    // 'created_by' => 1,
                    // 'updated_by' => 1,
                ]);
            }
        }
    }
}