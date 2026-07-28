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
use App\Models\Quran\Surah;
use App\Models\Region;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Services\IdQueryServices;
use App\Services\UserRoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StudentWithRelationsImport implements ToModel, WithHeadingRow, ShouldQueue, WithChunkReading/* , WithLimit */
{
    use Queueable, Importable;

    private $notes = 'تم الإنشاء من خلال استيراد البيانات. يرجى مراجعة البيانات والتأكد من صحتها.';
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
        'السورة' => 'surah',
        'الاية' => 'ayah',
        'عدد أجزاء السرد' => 'recitation_parts',
        'السرد من' => 'recitation_from',
        'السرد إلى' => 'recitation_to',
        'الحفظ من' => 'hifz_from',
        'الحفظ إلى' => 'hifz_to',
    ];

    public function __construct(private int $userId)
    {
    }

    public function __serialize()
    {
        return [
            'userId' => $this->userId,
            'notes' => $this->notes,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'] ?? null;
        $this->notes = $data['notes'] ?? 'تم الإنشاء من خلال استيراد البيانات. يرجى مراجعة البيانات والتأكد من صحتها.';
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function model(array $row)
    {
        $newData = [];
        foreach ($row as $key => $value) {
            $newKey = $this->map[$key] ?? $key;
            $newData[$newKey] = $value;
        }

        $branch = $this->addBranch($newData['branch']);
        $region = $this->addRegion($newData['region'], $branch);
        $mosque = $this->addMosque($newData['mosque'], $region);
        $center = $this->addCenter($newData['mosque'], $region, $mosque);

        try {
            $idQueryServices = new IdQueryServices();
            $user = $idQueryServices->firstOrCreateUser($newData['teacher_identity'], ['mosque_id' => $mosque->id]);
        } catch (\Throwable $th) {
            $this->addToFailedRows($row, 'خطأ في إنشاء أو تحديث المستخدم المرتبط بالمعلم: ' . $th->getMessage());
            return null;
        }


        $halaqa = $this->addHalaqa($user->full_name . ' - ' . $newData['mosque'], $center);

        $this->assignHalaqa($user, $halaqa);

        $currentUser = User::find($this->userId);
        if ($currentUser && $user->wasRecentlyCreated) {
            try {
                $user->submitForApproval($currentUser, "طلب إنشاء حساب معلم للحلقة: {$halaqa->name} من خلال استيراد البيانات");
            } catch (\Throwable $th) {
                //
            }
        }

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

    public function addToFailedRows(array $row, string $errorMessage)
    {
        throw new \InvalidArgumentException('يوجد خطأ في البيانات المدخلة' . ' ' . $errorMessage);
    }

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

    public function firstOrCreateStudent(array $row, array $newData, Mosque $mosque)
    {
        $guardian_type_id = $this->firstOrCreateConstant('guardian_type', 'بنفسه', $this->notes)->id;

        $identity = $newData['student_identity'];

        try {
            $idQueryServices = new IdQueryServices();
            $user = $idQueryServices->firstOrCreateUser($identity, ['mosque_id' => $mosque->id]);

            $currentUser = User::find($this->userId);
            if ($currentUser && $user->wasRecentlyCreated) {
                try {
                    $studentName = $newData['name'] ?? 'الطالب';
                    $studentName = trim($studentName) ?: 'الطالب';
                    $user->submitForApproval($currentUser, "طلب إنشاء حساب ولي أمر للطالب: {$studentName} من خلال استيراد البيانات");
                } catch (\Throwable $th) {
                    //
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
                $this->addToFailedRows($row, 'السورة(' . $newData['surah'] . ') غير موجودة في النظام');
                return null;
            }
            $data['surah_id'] = $Surah->id;
            $ayah = $newData['ayah'];
            if ($ayah && $ayah > $Surah->verses_count) {
                $this->addToFailedRows($row, 'الآية(' . $newData['ayah'] . ') غير موجودة في السورة(' . $newData['surah'] . ') المحددة');
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

    public function addBranch(string $branch_name)
    {
        $branch = Branch::firstOrCreate([
            'name' => $branch_name,
        ], [
            'notes' => $this->notes,
        ]);

        return $branch;
    }

    public function addRegion(string $region_name, Branch $branch)
    {
        $region = Region::firstOrCreate([
            'name' => $region_name,
            'branch_id' => $branch->id,
        ], [
            'notes' => $this->notes,
        ]);

        return $region;
    }

    public function addMosque(string $mosque_name, Region $region)
    {
        $mosque = Mosque::firstOrCreate([
            'name' => $mosque_name,
            'region_id' => $region->id,
        ], [
            'notes' => $this->notes,
        ]);

        return $mosque;
    }

    public function addCenter(string $center_name, Region $region, Mosque $mosque)
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

    public function addHalaqa(string $halaqa_name, Center $center)
    {
        $halaqa_type_id = $this->firstOrCreateConstant('halaqa_types', 'حفظ', $this->notes)->id;

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
    public function assignHalaqa(User $user, Halaqa $halaqa)
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
