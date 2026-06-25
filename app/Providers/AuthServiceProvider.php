<?php

namespace App\Providers;

use App\Models\AcademicQualification;
use App\Models\ApprovalRequest;
use App\Models\Branch;
use App\Models\Center;
use App\Models\Constant;
use App\Models\Halaqa;
use App\Models\HalaqaStudent;
use App\Models\Mosque;
use App\Models\PersonalCourse;
use App\Models\Quran\CustomJuz;
use App\Models\Region;
use App\Models\Student;
use App\Models\User;
use App\Models\UserRole;
use App\Policies\AcademicQualificationPolicy;
use App\Policies\ApprovalPolicy;
use App\Policies\BranchPolicy;
use App\Policies\CenterPolicy;
use App\Policies\ConstantPolicy;
use App\Policies\CustomJuzPolicy;
use App\Policies\HalaqaPolicy;
use App\Policies\HalaqaStudentPolicy;
use App\Policies\MosquePolicy;
use App\Policies\PersonalCoursePolicy;
use App\Policies\RegionPolicy;
use App\Policies\StudentPolicy;
use App\Policies\UserPolicy;
use App\Policies\UserRolePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Branch::class => BranchPolicy::class,
        Region::class => RegionPolicy::class,
        Center::class => CenterPolicy::class,
        User::class => UserPolicy::class,
        PersonalCourse::class => PersonalCoursePolicy::class,
        AcademicQualification::class => AcademicQualificationPolicy::class,
        Student::class => StudentPolicy::class,
        Halaqa::class => HalaqaPolicy::class,
        Mosque::class => MosquePolicy::class,
        Constant::class => ConstantPolicy::class,
        HalaqaStudent::class => HalaqaStudentPolicy::class,
        ApprovalRequest::class => ApprovalPolicy::class,
        CustomJuz::class => CustomJuzPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
