<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendances = Attendance::with(['attendable', 'halaqa', 'status'])
            ->when($request->halaqa_id, fn($q) => $q->where('halaqa_id', $request->halaqa_id))
            ->when($request->date, fn($q) => $q->where('date', $request->date))
            ->get();

        return response()->json($attendances);
    }

    public function store(Request $request)
    {
        $request->validate([
            'attendable_id' => 'required|integer',
            'attendable_type' => 'required|string',
            'halaqa_id' => 'required|integer',
            'date' => 'required|date',
            'status_id' => 'required|integer',
        ]);

        $exists = Attendance::where('attendable_id', $request->attendable_id)
            ->where('attendable_type', $request->attendable_type)
            ->where('halaqa_id', $request->halaqa_id)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Attendance already recorded for this person today'
            ], 422);
        }

        $attendance = Attendance::create($request->all());

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'attendance' => $attendance
        ]);
    }
    public function show(Attendance $attendance)
    {
        return response()->json($attendance, 200);
    }
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status_id' => 'sometimes|integer',
            'notes' => 'nullable|string',
        ]);

        $attendance->update($request->all());

        return response()->json([
            'message' => 'Attendance updated successfully',
            'attendance' => $attendance
        ]);
    }

    // حذف الحضور
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->json(['message' => 'Attendance deleted successfully']);
    }
}
