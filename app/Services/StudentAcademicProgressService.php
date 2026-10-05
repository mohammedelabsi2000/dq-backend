<?php

namespace App\Services;

use App\Enums\ResultStatus;
use App\Helpers\ConstantHelper;
use App\Models\Level;
use App\Models\LevelTrack;
use App\Models\LevelTrackSubject;
use App\Models\Plan;
use App\Models\Student;
use App\Models\StudentLevel;
use App\Models\StudentPlan;
use App\Models\StudentSubject;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentAcademicProgressService
{
    protected int $passedStatusId;
    public function __construct()
    {
        $this->passedStatusId = ResultStatus::Passed->id();
    }
    public function enrollInNextSubject(Student $student, StudentSubject $currentStudentSubject)
    {
        if ($this->checkLevelTrackIsCompleted($student, $currentStudentSubject->levelTrack)) {
            if ($this->checkLevelIsCompleted($student, $currentStudentSubject->level)) {
                if ($this->checkPlanIsCompleted($student, $currentStudentSubject->level->plan)) {
                    return null;
                } else {
                    $this->enrollStudentInNextLevel($student, $currentStudentSubject->level);
                }
            } else {

            }
        } else {
            $this->enrollStudentInNextSubject(
                $currentStudentSubject->level->plan,
                $currentStudentSubject->level,
                $currentStudentSubject->subject,
                $student
            );
        }


        return DB::transaction(function () use ($student, $currentStudentSubject) {

            $currentLevelTrack = $currentStudentSubject->levelTrack;
            $currentLevel = $currentLevelTrack->level;
            $currentTrack = $currentLevelTrack->track;

            /*
             * 1. Find next subject in the same level
             */
            $nextSubject = LevelTrackSubject::where('level_track_id', $currentLevelTrack->id)
                ->where('order', '>', $currentLevelTrack->order)
                ->orderBy('order')
                ->first();

            if ($nextSubject) {
                return $this->enrollStudentInSubject(
                    $student,
                    $currentLevel,
                    $nextSubject->subject
                );
            }

            /*
             * Check all subjects in this level
             */
            $passedSubjects = StudentSubject::where('student_id', $student->id)
                ->where('result_status_id', ConstantHelper::getConstantIdByKey('result_status', 'passed'))
                ->where('level_id', $currentLevel->id)
                ->get();


            /*
             * 2. Current subject is the last subject
             *    Find the next level
             */
            $nextLevel = Level::where('plan_id', $currentLevel->plan_id)
                ->where('order', '>', $currentLevel->order)
                ->orderBy('order')
                ->first();

            if (!$nextLevel) {
                // Student finished the plan
                return null;
            }

            /*
             * 3. Enroll student in next level
             */
            $this->enrollLevel($student, $nextLevel);

            /*
             * 4. Get first subject in the new level
             */
            $nextLevelTrack = LevelTrack::where('track');


            $firstSubject = $nextLevel->subjects()
                ->orderBy('subjects.id')
                ->first();

            if (!$firstSubject) {
                return null;
            }

            /*
             * 5. Enroll student in first subject
             */
            return $this->enrollStudentInSubject(
                $student,
                $nextLevel,
                $firstSubject
            );
        });
    }

    public function enrollLevel(Student $student, Level $level): StudentLevel
    {
        $studentPlan = StudentPlan::where('student_id', $student->id)
            ->where('plan_id', $level->plan_id)
            ->active()
            ->firstOrFail();
        $studentPlan->current_level_id = $level->id;
        $studentPlan->save();
        
        return StudentLevel::firstOrCreate([
            'student_id' => $student->id,
            'level_id' => $level->id,
        ], [
            'from_date' => Carbon::now(),
        ]);
    }
    public function enrollStudentInNextLevel(Student $student, Level $currentLevel)
    {
        $nextLevel = Level::where('plan_id', $currentLevel->plan_id)
            ->where('order', '>', $currentLevel->order)
            ->orderBy('order')
            ->first();

        if (!$nextLevel) {
            // Student finished the plan
            return null;
        }

        return $this->enrollLevel($student, $nextLevel);
    }

    /**
     * Summary of enrollStudentInNextSubject
     * @param Plan $plan
     * @param Level $level
     * @param Subject $subject
     * @param Student|null $student
     */
    protected function enrollStudentInNextSubject(Plan $plan, Level $level, Subject $subject, Student $student = null)
    {
        /*
         * Find where the current subject exists
         * inside the current level.
         *
         * Subject cannot be repeated in the same plan,
         * therefore this uniquely identifies its position.
         */
        $current = LevelTrackSubject::query()
            ->where('subject_id', $subject->id)
            ->whereHas('levelTrack', function ($query) use ($level) {
                $query->where('level_id', $level->id);
            })
            ->with('levelTrack')
            ->first();

        if (!$current) {
            throw new \RuntimeException(
                // "Subject [{$subject->id}] does not belong to Level [{$level->id}]."
                "المساق [{$subject->title}] لا ينتمي إلى المستوى [{$level->name}] في الخطة [{$plan->name}]."
            );
        }

        /*
         * 1. Next subject in the same track.
         */
        $next = LevelTrackSubject::query()
            ->where('level_track_id', $current->level_track_id)
            ->where('order', '>', $current->order)
            ->orderBy('order')
            ->first();

        if ($next) {
            return $this->enrollStudentInSubject(
                $student,
                $level,
                $next->subject
            );
        }

        /*
         * 2. No more subjects in the current track.
         *
         * Move to the next track in the same level.
         */
        /* $nextTrack = LevelTrack::query()
            ->where('level_id', $level->id)
            ->where('id', '>', $current->level_track_id)
            ->orderBy('id')
            ->first();

        if ($nextTrack) {

            $next = $nextTrack->levelTrackSubjects()
                ->orderBy('order')
                ->first();

            if ($next) {
                return $this->enrollStudentInSubject(
                    $student,
                    $level,
                    $next->subject
                );
            }
        } */

        /*
         * 3. No more tracks in the current level.
         *
         * Move to the next level.
         */
        $nextLevel = $plan->levels()
            ->where('order', '>', $level->order)
            ->orderBy('order')
            ->first();

        if (!$nextLevel) {
            // Plan completed.
            return null;
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /*
         * 4. First track of the next level.
         */
        $nextTrack = $nextLevel->levelTracks()
            ->orderBy('id')
            ->first();

        if (!$nextTrack) {
            return null;
        }

        /*
         * 5. First subject of the next level.
         */
        $next = $nextTrack->levelTrackSubjects()
            ->orderBy('order')
            ->first();

        if (!$next) {
            return null;
        }

        return $this->enrollStudentInSubject(
            $student,
            $nextLevel,
            $next->subject
        );
    }

    protected function enrollStudentInSubject(
        ?Student $student,
        Level $level,
        Subject $subject
    ) {
        if (!$student) {
            return [
                'level' => $level,
                'subject' => $subject,
            ];
        }

        return StudentSubject::firstOrCreate([
            'student_id' => $student->id,
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'result_status_id' => ResultStatus::InProgress->id(),
        ], [
            'from_date' => Carbon::now(),
        ]);
    }

    public function changeStatusSubject(
        StudentSubject $studentSubject,
        int $newStatusId,
        float $grade,
        ?int $teacherId = null,
        ?string $notes = null
    ) {
        DB::transaction(function () use ($studentSubject, $newStatusId, $grade) {
            $currentStatusId = $studentSubject->result_status_id;
            $update = [];
            if ($newStatusId != $currentStatusId) {
                $update['to_date'] = $studentSubject->to_date ?? Carbon::now();
            }
            if ($studentSubject->grade == null) {
                $update['grade'] = $grade;
                $update['grade_date'] = $studentSubject->grade_date ?? Carbon::now();
            }
            // فحص الحالة الحالية للمساق
            switch ($currentStatusId) {
                case ResultStatus::InProgress->id():
                    $update['result_status_id'] = $newStatusId;
                    if (
                        in_array($newStatusId, [
                            ResultStatus::Passed->id(),
                            ResultStatus::Failed->id(),
                        ])
                    ) {
                        // $studentSubject->update($update);
                    }
                    break;
                case ResultStatus::Failed->id():
                    # code...
                    break;
                case ResultStatus::Passed->id():
                    # code...
                    break;

                default:
                    # code...
                    break;
            }

            // فحص الحالة الجديدة للمساق
            switch ($newStatusId) {
                case ResultStatus::InProgress->id():
                    if ($newStatusId != $currentStatusId) {
                        StudentSubject::create([
                            'student_id' => $studentSubject->student_id,
                            'subject_id' => $studentSubject->subject_id,
                            'level_id' => $studentSubject->level_id,
                            'from_date' => Carbon::now(),
                            'result_status_id' => $newStatusId,
                        ]);
                    }
                    break;
                case ResultStatus::Failed->id():
                    // إذا الحالة الجديدة راسب يتم إنهاء تنسيب الطالب للمساق وإعادة تنسيبه في سجل جديد
                    StudentSubject::create([
                        'student_id' => $studentSubject->student_id,
                        'subject_id' => $studentSubject->subject_id,
                        'level_id' => $studentSubject->level_id,
                        'from_date' => Carbon::now(),
                        'result_status_id' => ResultStatus::InProgress->id(),
                    ]);
                    break;
                case ResultStatus::Passed->id():
                    if ($grade == null) {
                        $update['grade'] = $studentSubject->subject->success_mark;
                    }
                    $this->enrollInNextSubject($studentSubject->student, $studentSubject);
                    break;

                default:

                    break;
            }

            $studentSubject->update($update);

            StudentSubject::create([
                'student_id' => $studentSubject->student_id,
                'subject_id' => $studentSubject->subject_id,
                'level_id' => $studentSubject->level_id,
                'teacher_id' => $studentSubject->teacher_id,
                'result_status_id' => $newStatusId,
                'grade' => $grade,
            ]);
        });
    }

    public function updateGrade(StudentSubject $studentSubject, float $grade, )
    {
        if (
            !in_array($studentSubject->result_status_id, [
                ResultStatus::Passed->id(),
                ResultStatus::Failed->id(),
            ])
        ) {
            throw new \RuntimeException(
                "يجب أن تكون حالة الطالب ناجح أو راسب ليتم ترصيد درجة له"
            );
        }
        $studentSubject->update([
            'grade' => $grade,
            'grade_date' => $studentSubject->to_date ?? Carbon::now(),
        ]);
    }

    // checkLevelTrackCompletion
    public function checkLevelTrackIsCompleted(Student $student, LevelTrack $levelTrack)
    {
        $subjectsInLevelTrack = $levelTrack->levelTrackSubjects()->pluck('subject_id')->toArray();
        $passedSubjects = StudentSubject::where('student_id', $student->id)
            ->where('level_id', $levelTrack->level_id)
            ->where('result_status_id', ResultStatus::Passed->id())
            ->pluck('subject_id')
            ->toArray();

        return empty(array_diff($subjectsInLevelTrack, $passedSubjects));
    }

    public function checkLevelIsCompleted(Student $student, Level $level)
    {

        $subjectsInLevel = $level->subjects()->pluck('id')->toArray();
        $passedSubjects = StudentSubject::where('student_id', $student->id)
            ->where('level_id', $level->id)
            ->where('result_status_id', ResultStatus::Passed->id())
            ->pluck('subject_id')
            ->toArray();

        return empty(array_diff($subjectsInLevel, $passedSubjects));
    }

    public function checkPlanIsCompleted(Student $student, Plan $plan)
    {
        $levelsInPlan = $plan->levels()->pluck('id')->toArray();
        $completedLevels = StudentLevel::where('student_id', $student->id)
            ->whereIn('level_id', $levelsInPlan)
            ->get()
            ->filter(function ($studentLevel) use ($student) {
                return $this->checkLevelIsCompleted($student, $studentLevel->level);
            })
            ->pluck('level_id')
            ->toArray();

        return empty(array_diff($levelsInPlan, $completedLevels));
    }
}