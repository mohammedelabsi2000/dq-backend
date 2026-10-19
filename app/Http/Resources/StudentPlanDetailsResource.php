<?php

namespace App\Http\Resources;

use App\Models\Level;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentPlanDetailsResource extends JsonResource
{

    protected $studentLevelsPivots;
    protected $currentLevel;

    public function __construct($resource,$currentLevel, $studentLevelsPivots)
    {
        parent::__construct($resource);
        $this->studentLevelsPivots = $studentLevelsPivots;
        $this->currentLevel = $currentLevel;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // $plan = $this->resource;
        $studentLevelsPivots = $this->studentLevelsPivots;
        return [
            'plan' => new PlanResource($this),
            'current_level' => $this->currentLevel,
            'levels' => $this->levels()->get()
                ->map(function ($level) use ($studentLevelsPivots) {
                    $pivot = $studentLevelsPivots->get($level->id);
                    return [
                        'level' => new LevelResource($level),
                        // 'id' => $level->id,
                        // 'name' => $level->name,
                        'student_level' => [
                            'from_date' => $pivot ? $pivot->from_date : null,
                            'to_date' => $pivot ? $pivot->to_date : null,
                            'notes' => $pivot ? $pivot->notes : null,
                        ]
                    ];
                })
        ];
        // return [
        //     // 'student' => new StudentResource($this->resource),
        //     // This is object of StudentPlan model, not an array, so we can use the StudentPlanResource to transform it
        //     'student_plan' => $this->when(
        //         $this->studentPlans->isNotEmpty(),
        //         function () use ($request) {
        //             $studentPlan = $this->studentPlans->first();

        //             $studentLevels = $this->studentLevels
        //                 ->filter(function ($studentLevel) use ($studentPlan) {
        //                     return $studentLevel->level->plan_id === $studentPlan->plan_id;
        //                 });

        //             return [
        //                 ...(new StudentPlanResource($studentPlan))
        //                     ->toArray($request),

        //                 'levels' => StudentLevelResource::collection(
        //                     $studentLevels
        //                 ),
        //             ];
        //         }
        //     ),

        //     /* 'student_plan' => $this->studentPlans
        //         ->map(function ($studentPlan) use ($request) {

        //             $studentLevels = $this->studentLevels
        //                 ->filter(function ($studentLevel) use ($studentPlan) {
        //                     return $studentLevel->level->plan_id
        //                         === $studentPlan->plan_id;
        //                 });

        //             return [
        //                 ...(new StudentPlanResource($studentPlan))
        //                     ->toArray($request),

        //                 'levels' => StudentLevelResource::collection(
        //                     $studentLevels
        //                 ),
        //             ];
        //         }), */
        // ];
    }
}
