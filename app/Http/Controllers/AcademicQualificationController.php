<?php

namespace App\Http\Controllers;

use App\Models\AcademicQualification;
use App\Models\Constant;
use Illuminate\Http\Request;

class AcademicQualificationController extends Controller
{
    /**
     * Display a listing of academic qualifications.
     */
    public function index()
    {
        $qualifications = AcademicQualification::with(['academicDegree', 'major', 'person'])
            ->latest()
            ->paginate(15);

        return view('academic_qualifications.index', compact('qualifications'));
    }

    /**
     * Show the form for creating a new academic qualification.
     */
    public function create()
    {
        $degrees = Constant::where('constant_type_id', 1)->get(); // تعديل حسب نوع الثوابت
        $majors = Constant::where('constant_type_id', 2)->get();  // تعديل حسب نوع الثوابت

        return view('academic_qualifications.create', compact('degrees', 'majors'));
    }

    /**
     * Store a newly created academic qualification in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'academic_degree_id' => 'required|exists:constants,id',
            'major_id' => 'required|exists:constants,id',
            'person_type' => 'required|string',
            'person_id' => 'required|integer',
            'detail' => 'nullable|string',
            'date_graduate' => 'nullable|date',
            'certificate_link' => 'nullable|string',
            'educational_institution' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        AcademicQualification::create($request->all());

        return redirect()->route('academic-qualifications.index')
            ->with('success', 'Academic qualification created successfully.');
    }

    /**
     * Show a specific academic qualification.
     */
    public function show(AcademicQualification $academicQualification)
    {
        return view('academic_qualifications.show', compact('academicQualification'));
    }

    /**
     * Show the form for editing a specific academic qualification.
     */
    public function edit(AcademicQualification $academicQualification)
    {
        $degrees = Constant::where('constant_type_id', 1)->get();
        $majors = Constant::where('constant_type_id', 2)->get();

        return view('academic_qualifications.edit', compact('academicQualification', 'degrees', 'majors'));
    }

    /**
     * Update a specific academic qualification.
     */
    public function update(Request $request, AcademicQualification $academicQualification)
    {
        $request->validate([
            'academic_degree_id' => 'required|exists:constants,id',
            'major_id' => 'required|exists:constants,id',
            'person_type' => 'required|string',
            'person_id' => 'required|integer',
            'detail' => 'nullable|string',
            'date_graduate' => 'nullable|date',
            'certificate_link' => 'nullable|string',
            'educational_institution' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $academicQualification->update($request->all());

        return redirect()->route('academic-qualifications.index')
            ->with('success', 'Academic qualification updated successfully.');
    }

    /**
     * Delete a specific academic qualification.
     */
    public function destroy(AcademicQualification $academicQualification)
    {
        $academicQualification->delete();

        return redirect()->route('academic-qualifications.index')
            ->with('success', 'Academic qualification deleted successfully.');
    }
}
