<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LevelRequest;
use App\Http\Resources\LevelResource;
use App\Models\Level;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class LevelController extends Controller
{
    public function index(Plan $plan): JsonResponse
    {
        $levels = $plan->levels()->with('levelTracks.track')->get();
        return $this->success(LevelResource::collection($levels));
    }

    public function store(LevelRequest $request): JsonResponse
    {
        $level = Level::create($request->validated());

        return $this->success(new LevelResource($level->load('plan')), 'تم إنشاء المستوى بنجاح', 201);
    }

    public function show(Level $level): JsonResponse
    {
        $level->load('plan', 'levelTracks.track', 'levelTracks.levelTrackSubjects.subject');

        return $this->success(new LevelResource($level));
    }

    public function update(LevelRequest $request, Level $level): JsonResponse
    {
        $level->update($request->validated());

        return $this->success(new LevelResource($level), 'تم تحديث المستوى بنجاح');
    }

    public function destroy(Level $level): JsonResponse
    {
        $level->delete();

        return $this->success(null, 'تم حذف المستوى بنجاح');
    }

    public function reorder(Plan $plan): JsonResponse
    {
        $items = request()->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:levels,id'],
            'items.*.order' => ['required', 'integer', 'min:1'],
        ])['items'];

        foreach ($items as $item) {
            Level::where('id', $item['id'])
                ->where('plan_id', $plan->id)
                ->update(['order' => $item['order']]);
        }

        return $this->success(null, 'تم تحديث الترتيب بنجاح');
    }
}
