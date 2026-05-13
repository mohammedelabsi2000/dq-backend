<?php

namespace App\Imports\Student;

use App\Enums\HalaqaReferenceType;
use App\Imports\Student\Concerns\HasColumnMap;
use App\Models\Branch;
use App\Models\Center;
use App\Models\Constant;
use App\Models\ConstantType;
use App\Models\Halaqa;
use App\Models\HalaqaStudent;
use App\Models\Mosque;
use App\Models\Quran\Surah;
use App\Models\Region;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Services\IdQueryServices;
use App\Services\UserRoleService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentWithRelationsImport implements ToModel, WithHeadingRow/* , WithLimit */
{
    use HasColumnMap;
    protected Request $request;
    public array $headings = [];
    public array $failedRows = [];
    public array $errors = [];
    private $notes = 'تم الإنشاء من خلال استيراد البيانات. يرجى مراجعة البيانات والتأكد من صحتها.';
    private IdQueryServices $idQueryServices;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->idQueryServices = new IdQueryServices();
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
            if (!in_array('الأخطاء', $this->headings)) {
                $this->headings[] = 'الأخطاء';
            }
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

        $branch = $this->addBranch($newData['branch']);
        $region = $this->addRegion($newData['region'], $branch);
        $mosque = $this->addMosque($newData['mosque'], $region);
        $center = $this->addCenter($newData['mosque'], $region, $mosque);

        try {
            $user = $this->idQueryServices->firstOrCreateUser($newData['teacher_identity'], ['mosque_id' => $mosque->id]);
        } catch (\Throwable $th) {
            $this->addToFailedRows($row, 'خطأ في إنشاء أو تحديث المستخدم المرتبط بالمعلم: ' . $th->getMessage());
            return null;
        }
        $halaqa = $this->addHalaqa($user->full_name . ' - ' . $newData['mosque'], $center);

        $this->assignHalaqa($user, $halaqa);

        // Submit for approval if current user is available and user was just created
        $currentUser = $this->request->get('user');
        if ($currentUser && $user->wasRecentlyCreated) {
            try {
                $user->submitForApproval($currentUser, "طلب إنشاء حساب معلم للحلقة: {$halaqa->name} من خلال استيراد البيانات");
            } catch (\Throwable $th) {
                // Log error but don't fail the import
                // In a real implementation, you might want to log this
            }
        }

        // $guardian_type_id = $this->firstOrCreateConstant('guardian_type', 'محفظ', $this->notes)->id;



        $student = $this->firstOrCreateStudent($row, $newData, $mosque);

        if ($student) {
            HalaqaStudent::where('student_id', $student->id)
                ->where('halaqa_id', '!=', $halaqa->id)
                ->whereNull('to_date')
                ->update(['to_date' => now()]);

            HalaqaStudent::whereNull('to_date')->firstOrCreate(
                [
                    'halaqa_id' => $halaqa->id,
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
     * @param array $row
     * @param array $newData
     * @param Mosque $mosque
     */
    public function firstOrCreateStudent(array $row, array $newData, Mosque $mosque)
    {
        $guardian_type_id = $this->firstOrCreateConstant('guardian_type', 'بنفسه', $this->notes)->id;

        $identity = $newData['student_identity'];

        try {
            $user = $this->idQueryServices->firstOrCreateUser($identity, ['mosque_id' => $mosque->id]);

            // Submit for approval if current user is available and user was just created
            $currentUser = $this->request->get('user');
            if ($currentUser && $user->wasRecentlyCreated) {
                try {
                    $studentName = $newData['name'] ?? 'الطالب';
                    $studentName = trim($studentName) ?: 'الطالب';
                    $user->submitForApproval($currentUser, "طلب إنشاء حساب ولي أمر للطالب: {$studentName} من خلال استيراد البيانات");
                } catch (\Throwable $th) {
                    // Log error but don't fail the import
                    // In a real implementation, you might want to log this
                }
            }
        } catch (\Throwable $th) {
            $this->addToFailedRows($row, 'خطأ في إنشاء أو تحديث المستخدم المرتبط بالولي: ' . $th->getMessage());
            return null;
        }

        $student = Student::where('identity', $identity)->first();

        $data = [
            'guardian_id' => $identity,
            'guardian_type_id' => $guardian_type_id,
            'mosque_id' => $mosque->id,
        ];

        if ($newData['surah']) {
            $Surah = Surah::where('name_ar', $newData['surah'])->first();
            if (!$Surah) {
                $this->addToFailedRows($row, 'السورة غير موجودة في النظام');
                return null;
            }
            $data['surah_id'] = $Surah->id;
            $ayah = $newData['ayah'];
            if ($ayah && $ayah > $Surah->verses_count) {
                $this->addToFailedRows($row, 'الآية غير موجودة في السورة المحددة');
                return null;
            } elseif ($ayah) {
                $data['end_aya'] = $ayah;
            }
        }

        if ($newData['recitation_from'] && $newData['recitation_to']) {
            $data['completed_juz'] = implode(',', range($newData['recitation_from'], $newData['recitation_to']));
        }

        if ($newData['hifz_from'] && $newData['hifz_to']) {
            $data['memorized_juz'] = implode(',', range($newData['hifz_from'], $newData['hifz_to']));
        }

        if ($student) {
            $student->update([
                ...$data,
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
            ...$data,
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
            'is_approved' => false,
            'is_active' => false,
        ]);


        return $user;
    }

    private function addToFailedRows(array $row, string $errorMessage)
    {
        throw new \InvalidArgumentException('يوجد خطأ في البيانات المدخلة' . ' ' . $errorMessage);
        $this->failedRows[] = $row;
        $this->failedRows[count($this->failedRows) - 1]['الأخطاء'] = $errorMessage;
    }

    /**
     * Add a new branch
     * @param string $branch_name
     * @return Branch
     */
    private function addBranch(string $branch_name)
    {
        $branch = Branch::firstOrCreate([
            'name' => $branch_name,
        ], [
            'notes' => $this->notes,
        ]);

        return $branch;
    }

    /**
     * Add a new region
     * @param string $region_name
     * @param Branch $branch
     * @return Region
     */
    private function addRegion(string $region_name, Branch $branch)
    {
        $region = Region::firstOrCreate([
            'name' => $region_name,
            'branch_id' => $branch->id,
        ], [
            'notes' => $this->notes,
        ]);

        return $region;
    }

    /**
     * Add a new mosque
     * @param string $mosque_name
     * @param Region $region
     * @return Mosque
     */
    private function addMosque(string $mosque_name, Region $region)
    {
        $mosque = Mosque::firstOrCreate([
            'name' => $mosque_name,
            'region_id' => $region->id,
        ], [
            'notes' => $this->notes,
        ]);

        return $mosque;
    }

    /**
     * Add a new center
     * @param string $center_name
     * @param Region $region
     * @param Mosque $mosque
     * @return Center
     */
    private function addCenter(string $center_name, Region $region, Mosque $mosque)
    {
        $center = Center::firstOrCreate([
            'name' => $center_name,
            'region_id' => $region->id,
        ], [
            'mosque_id' => $mosque->id,
            'notes' => $this->notes,
        ]);

        return $center;
    }

    /**
     * Add a new halaqa
     * @param string $halaqa_name
     * @param Center $center
     * @return Halaqa
     */
    private function addHalaqa(string $halaqa_name, Center $center)
    {
        $halaqa_type_id = $this->firstOrCreateConstant('halaqa_type', 'حفظ', $this->notes)->id;

        $halaqa = Halaqa::firstOrCreate([
            'name' => $halaqa_name,
            'reference_type' => HalaqaReferenceType::Center->code(),
            'reference_id' => $center->id,
        ], [
            'description' => $this->notes,
            'type_id' => $halaqa_type_id,
        ]);

        return $halaqa;
    }

    /**
     * Assign halaqa to teacher user
     * @param User $user
     * @param Halaqa $halaqa
     * @return void
     */
    private function assignHalaqa(User $user, Halaqa $halaqa)
    {
        $role = Role::firstOrCreate(['name' => 'معلم', 'guard_name' => 'sanctum'], ['notes' => $this->notes]);

        $userRoleService = new UserRoleService();
        $userRoleService->assignRolesWithScopes(
            $user,
            [$role->id],
            [
                ['type' => 'halaqa', 'id' => $halaqa->id],
            ]
        );
    }
}
