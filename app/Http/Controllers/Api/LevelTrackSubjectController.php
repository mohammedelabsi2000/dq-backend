<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LevelTrackCourse\LevelTrackSubjectRequest;
use App\Http\Resources\LevelTrackSubjectResource;
use App\Models\LevelTrack;
use App\Models\LevelTrackSubject;

class LevelTrackSubjectController extends Controller
{
    // ========================
    // GET /level-tracks/{levelTrack}/subjects
    // ========================
    public function index(LevelTrack $levelTrack)
    {
        $subjects = $levelTrack->levelTrackSubjects()
                               ->with('subject')
                               ->orderBy('order')
                               ->get();

        return $this->success(
            LevelTrackSubjectResource::collection($subjects),
            'بيانات المساقات'
        );
    }

    // ========================
    // POST /level-tracks/{levelTrack}/subjects
    // إضافة عدة مساقات دفعة واحدة
    // ========================
    public function store(LevelTrackSubjectRequest $request, LevelTrack $levelTrack)
    {
        $now = now();

        $toInsert = collect($request->subjects)->map(fn($s) => [
            'level_track_id' => $levelTrack->id,
            'subject_id'     => $s['subject_id'],
            'is_required'    => $s['is_required'] ?? true,
            'order'          => $s['order'] ?? null,
            'created_at'     => $now,
            'updated_at'     => $now,
        ])->toArray();

        LevelTrackSubject::insert($toInsert);

        $subjects = $levelTrack->levelTrackSubjects()
                               ->with('subject')
                               ->orderBy('order')
                               ->get();

        return $this->success(
            LevelTrackSubjectResource::collection($subjects),
            'تم إضافة ' . count($toInsert) . ' مساق بنجاح',
            201
        );
    }

    // ========================
    // GET /level-track-subjects/{levelTrackSubject}
    // ========================
    public function show(LevelTrackSubject $levelTrackSubject)
    {
        $levelTrackSubject->load('subject', 'levelTrack.level', 'levelTrack.track');

        return $this->success(
            new LevelTrackSubjectResource($levelTrackSubject),
            'بيانات المساق'
        );
    }

    // ========================
    // PUT /level-tracks/{levelTrack}/subjects
    // sync كامل للقائمة — إضافة / تعديل / حذف
    // ========================
    public function update(LevelTrackSubjectRequest $request, LevelTrack $levelTrack)
    {
        $incoming = collect($request->subjects);

        $existing = $levelTrack->levelTrackSubjects()
                               ->get()
                               ->keyBy('subject_id');

        $incomingIds = $incoming->pluck('subject_id')->toArray();
        $existingIds = $existing->keys()->toArray();

        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            $levelTrack->levelTrackSubjects()
                       ->whereIn('subject_id', $toDelete)
                       ->delete();
        }

        foreach ($incoming as $s) {
            if ($existing->has($s['subject_id'])) {
                $existing[$s['subject_id']]->update([
                    'order'       => $s['order'] ?? null,
                    'is_required' => $s['is_required'] ?? true,
                ]);
            } else {
                LevelTrackSubject::create([
                    'level_track_id' => $levelTrack->id,
                    'subject_id'     => $s['subject_id'],
                    'order'          => $s['order'] ?? null,
                    'is_required'    => $s['is_required'] ?? true,
                ]);
            }
        }

        $subjects = $levelTrack->levelTrackSubjects()
                               ->with('subject')
                               ->orderBy('order')
                               ->get();

        return $this->success(
            LevelTrackSubjectResource::collection($subjects),
            'تم تحديث المساقات بنجاح'
        );
    }

    // ========================
    // DELETE /level-track-subjects/{levelTrackSubject}
    // حذف مساق واحد
    // ========================
    public function destroy(LevelTrackSubject $levelTrackSubject)
    {
        $levelTrackSubject->delete();

        return $this->success(
            null,
            'تم حذف المساق من المسار بنجاح'
        );
    }
}