<?php

namespace App\Http\Controllers\Api;

use App\Enums\SuccessValueType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\SubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use App\Models\Track;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Subject::class);

        $query = Subject::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['title', 'sub_title'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $subjects = $query->with(['subjectType', 'subjectRequirements'])->get();

        return $this->successWithPagination(
            SubjectResource::collection($subjects),
            $q,
            'success',
            200
        );
    }

    public function store(SubjectRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Encode numeric arrays as JSON numbers without quotes, so they are stored as JSON arrays in the database
        $data['juzs'] = json_encode(
            array_map('intval', $data['juzs'] ?? [])
        );
        $data['surahs'] = json_encode(
            array_map('intval', $data['surahs'] ?? [])
        );
        $data['verses'] = json_encode(
            array_map('intval', $data['verses'] ?? [])
        );
        $data['pages'] = json_encode(
            array_map('intval', $data['pages'] ?? [])
        );

        $subject = Subject::create($data);

        $subjectRequirements = $data['subject_requirements'] ?? [];
        foreach ($subjectRequirements as $requirement) {
            $subject->subjectRequirements()->create([
                'success_value' => $requirement['success_value'],
                'success_value_type' => $requirement['success_value_type'],
                'weight' => $requirement['weight'] ?? null,
            ]);
        }

        return $this->success(new SubjectResource($subject->load('track')), 'تم إنشاء المساق بنجاح', 201);
    }

    public function show(Subject $subject): JsonResponse
    {
        $this->authorize('view', $subject);

        $subject->load('track');

        return $this->success(new SubjectResource($subject));
    }

    public function update(SubjectRequest $request, Subject $subject): JsonResponse
    {
        $data = $request->validated();

        $data['juzs'] = json_encode(
            array_map('intval', $data['juzs'] ?? [])
        );
        $data['surahs'] = json_encode(
            array_map('intval', $data['surahs'] ?? [])
        );
        $data['verses'] = json_encode(
            array_map('intval', $data['verses'] ?? [])
        );
        $data['pages'] = json_encode(
            array_map('intval', $data['pages'] ?? [])
        );

        $subject->update($data);

        $subjectRequirements = $data['subject_requirements'] ?? [];

        // Sync the subject requirements: delete those not in the request, update existing ones, and create new ones
        $existingRequirementTypes = $subject->subjectRequirements()->pluck('success_value_type')->toArray();
        $newRequirementTypes = array_filter(array_column($subjectRequirements, 'success_value_type'));

        // Delete requirements that are not in the new data
        $requirementsToDelete = array_diff($existingRequirementTypes, $newRequirementTypes);
        $subject->subjectRequirements()->whereIn('success_value_type', $requirementsToDelete)->delete();

        // Update existing requirements and create new ones
        foreach ($subjectRequirements as $requirement) {
            $subjectRequirement = $subject->subjectRequirements()->where('success_value_type', $requirement['success_value_type'])->first();

            if ($subjectRequirement) {
                // Update existing requirement
                $subjectRequirement->update([
                    'success_value' => $requirement['success_value'],
                    'weight' => $requirement['weight'] ?? null,
                ]);
            } else {
                // Create new requirement
                $subject->subjectRequirements()->create([
                    'success_value' => $requirement['success_value'],
                    'success_value_type' => $requirement['success_value_type'],
                    'weight' => $requirement['weight'] ?? null,
                ]);
            }
        }

        return $this->success(new SubjectResource($subject), 'تم تحديث المساق بنجاح', 200);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $this->authorize('delete', $subject);

        $subject->delete();

        return $this->success(null, 'تم حذف المساق بنجاح', 200);
    }

    public function subject_requirement_types()
    {
        $this->authorize('viewAny', Subject::class);
        // Get the types(label, value) from the SuccessValueType enum
        $types = array_map(function ($type) {
            return [
                'label' => $type->label(),
                'value' => $type->value,
            ];
        }, SuccessValueType::cases());

        return $this->success($types, 'success', 200);
    }
}
