<?php

namespace App\Imports\Student;

use App\Enums\HalaqaReferenceType;
use App\Models\Branch;
use App\Models\Center;
use App\Models\Constant;
use App\Models\ConstantType;
use App\Models\Halaqa;
use App\Models\HalaqaStudent;
use App\Models\Mosque;
use App\Models\Region;
use App\Models\Student;
use App\Models\User;
use App\Services\IdQueryServices;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentWithRelationsImport implements ToModel, WithHeadingRow/* , WithLimit */
{
    protected Request $request;
    public array $headings = [];
    public array $failedRows = [];
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
    private $notes = 'تم الإنشاء من خلال استيراد البيانات. يرجى مراجعة البيانات والتأكد من صحتها.';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /* public function limit(): int
    {
        return 240; // عدد الصفوف
    } */

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (empty($this->headings)) {
            $this->headings = array_keys($row);
            $this->headings[] = 'الأخطاء'; // عمود إضافي لتسجيل الأخطاء
        }

        $newData = [];
        foreach ($row as $key => $value) {
            $newKey = $this->map[$key] ?? $key; // لو ما في mapping خليه زي ما هو
            $newData[$newKey] = $value;
        }

        /* if (!$newData['branch']) {
            $this->addToFailedRows($row, 'اسم الفرع مطلوب');
            return null; // ما في فرع، ما نقدر نكمل
        }
        if (!$newData['region']) {
            $this->addToFailedRows($row, 'اسم المحلية مطلوب');
            return null; // ما في محلية، ما نقدر نكمل
        }
        if (!$newData['mosque']) {
            $this->addToFailedRows($row, 'اسم المسجد/المركز مطلوب');
            return null; // ما في مسجد، ما نقدر نكمل
        }
        if (!$newData['teacher_identity']) {
            $this->addToFailedRows($row, 'رقم هوية المعلم مطلوب');
            return null; // ما في معلم، ما نقدر نكمل
        }
        if (!$newData['student_identity']) {
            $this->addToFailedRows($row, 'رقم هوية الطالب مطلوب');
            return null; // ما في هوية للطالب، ما نقدر نكمل
        } */

        $branch_id = $this->addBranch(/* $newData['branch'] */ 'شرق غزة');
        $region_id = $this->addRegion($newData['region'], $branch_id);
        $mosque_id = $this->addMosque($newData['mosque'], $region_id);
        $center_id = $this->addCenter($newData['mosque'], $region_id, $mosque_id);

        try {
            $user = $this->firstOrCreateUser($row, $newData['teacher_identity'], $mosque_id);
        } catch (\Throwable $th) {
            $this->addToFailedRows($row, 'خطأ في إنشاء أو تحديث المستخدم المرتبط بالمعلم: ' . $th->getMessage());
            return null;
        }
        $halaqa_id = $this->addHalaqa($user->full_name . ' - ' . $newData['mosque'], $center_id);


        // TODO: Assign roles and scopes

        // $guardian_type_id = $this->firstOrCreateConstant('guardian_type', 'محفظ', $this->notes)->id;

        $guardian_type_id = $this->firstOrCreateConstant('guardian_type', 'بنفسه', $this->notes)->id;

        $student = $this->firstOrCreateStudent($row, $newData['student_identity'], $newData['student_identity'], $guardian_type_id, $mosque_id);

        if ($student) {
            HalaqaStudent::where('student_id', $student->id)
                ->where('halaqa_id', '!=', $halaqa_id)
                ->whereNull('to_date')
                ->update(['to_date' => now()]);

            HalaqaStudent::whereNull('to_date')->firstOrCreate(
                [
                    'halaqa_id' => $halaqa_id,
                    'student_id' => $student->id,
                ],
                [
                    'from_date' => now(),
                    'enrollment_status_id' => $this->firstOrCreateConstant('enrollment_status', 'منتظم', $this->notes)->id,
                ]
            );
        }

        return null;
    }

    /**
     * Summary of firstOrCreateConstant
     * @param string $constant_type
     * @param string $name
     * @param string|null $notes
     * @return Constant|\Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreateConstant(string $constant_type, string $name, ?string $notes = null)
    {
        $constant_type_id = ConstantType::firstOrCreate(
            ['name' => $constant_type],
            ['notes' => $notes]
        )->id;

        $constant = Constant::firstOrCreate(
            ['name' => $name, 'constant_type_id' => $constant_type_id],
            ['notes' => $notes]
        );

        return $constant;
    }

    /**
     * First or create student by identity
     * @param int $identity
     */
    public function firstOrCreateStudent(array $row, int $identity, int $guardian_id, int $guardian_type_id, int $mosque_id)
    {
        try {
            $this->firstOrCreateUser($row, $guardian_id, $mosque_id);
        } catch (\Throwable $th) {
            $this->addToFailedRows($row, 'خطأ في إنشاء أو تحديث المستخدم المرتبط بالولي: ' . $th->getMessage());
            return null;
        }

        $student = Student::where('identity', $identity)->first();

        if ($student) {
            $student->update([
                'guardian_id' => $guardian_id,
                'guardian_type_id' => $guardian_type_id,
                'mosque_id' => $mosque_id,
            ]);
            return $student;
        }

        try {
            $personData = (new IdQueryServices())->get($identity);
        } catch (\Throwable $th) {
            $this->addToFailedRows($row, 'خطأ في جلب بيانات الشخص: ' . $th->getMessage());
            return null;
        }

        $student = Student::create([
            'identity' => $identity,
            'fName' => $personData['CI_FIRST_ARB'] ?? null,
            'sName' => $personData['CI_FATHER_ARB'] ?? null,
            'thName' => $personData['CI_GRAND_FATHER_ARB'] ?? null,
            'family' => $personData['CI_FAMILY_ARB'] ?? null,
            'dob' => str_replace('/', '-', $personData['CI_BIRTH_DT']) ?? null,
            'gender' => $personData['SEX'] ?? null,
            'guardian_id' => $guardian_id,
            'guardian_type_id' => $guardian_type_id,
            'mosque_id' => $mosque_id,
        ]);

        return $student;
    }

    /**
     * First or create user by identity
     * @param array $row
     * @param int $identity
     * @param int $mosque_id
     * @return User|\Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreateUser(array $row, int $identity, int $mosque_id): User
    {
        $user = User::where('identity', $identity)->first();

        if ($user) {
            $user->update([
                'mosque_id' => $mosque_id,
            ]);
            return $user;
        }

        $personData = (new IdQueryServices())->get($identity);

        $user = User::create([
            'identity' => $identity,
            'fName' => $personData['CI_FIRST_ARB'] ?? null,
            'sName' => $personData['CI_FATHER_ARB'] ?? null,
            'thName' => $personData['CI_GRAND_FATHER_ARB'] ?? null,
            'family' => $personData['CI_FAMILY_ARB'] ?? null,
            'dob' => str_replace('/', '-', $personData['CI_BIRTH_DT']) ?? null,
            'gender' => $personData['SEX'] ?? null,
            'mosque_id' => $mosque_id,
            'email' => $identity . '@tahfiz.com',
            'password' => Hash::make('12345678'),
        ]);

        return $user;
    }

    private function addToFailedRows(array $row, string $errorMessage)
    {
        throw new \InvalidArgumentException('يوجد خطأ في البيانات المدخلة');
        $this->failedRows[] = $row;
        $this->failedRows[count($this->failedRows) - 1]['الأخطاء'] = $errorMessage;
    }

    /**
     * Add a new branch
     * @param string $branch_name
     * @return int
     */
    private function addBranch(string $branch_name)
    {
        $branch_id = Branch::firstOrCreate([
            'name' => $branch_name,
        ], [
            'notes' => $this->notes,
        ])->id;

        return $branch_id;
    }

    /**
     * Add a new region
     * @param string $region_name
     * @param int $branch_id
     * @return int
     */
    private function addRegion(string $region_name, int $branch_id)
    {
        $region_id = Region::firstOrCreate([
            'name' => $region_name,
            'branch_id' => $branch_id,
        ], [
            'notes' => $this->notes,
        ])->id;

        return $region_id;
    }

    /**
     * Add a new mosque
     * @param string $mosque_name
     * @param int $region_id
     * @return int
     */
    private function addMosque(string $mosque_name, int $region_id)
    {
        $mosque_id = Mosque::firstOrCreate([
            'name' => $mosque_name,
            'region_id' => $region_id,
        ], [
            'notes' => $this->notes,
        ])->id;

        return $mosque_id;
    }

    /**
     * Add a new center
     * @param string $center_name
     * @param int $region_id
     * @param int $mosque_id
     * @return int
     */
    private function addCenter(string $center_name, int $region_id, int $mosque_id)
    {
        $center_id = Center::firstOrCreate([
            'name' => $center_name,
            'region_id' => $region_id,
        ], [
            'mosque_id' => $mosque_id,
            'notes' => $this->notes,
        ])->id;

        return $center_id;
    }

    /**
     * Add a new halaqa
     * @param string $halaqa_name
     * @param int $center_id
     * @return int
     */
    private function addHalaqa(string $halaqa_name, int $center_id)
    {
        $halaqa_type_id = $this->firstOrCreateConstant('halaqa_type', 'حفظ', $this->notes)->id;

        $halaqa_id = Halaqa::firstOrCreate([
            'name' => $halaqa_name,
            'reference_type' => HalaqaReferenceType::Center->code(),
            'reference_id' => $center_id,
        ], [
            'description' => $this->notes,
            'type_id' => $halaqa_type_id,
        ])->id;

        return $halaqa_id;
    }
}
