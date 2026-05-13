<?php

namespace App\Imports;

use App\Models\Student;
use App\Services\StudentService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentsImport implements ToModel, WithHeadingRow
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // dd($this->request);
        $date = $row['تاريخ الميلاد'] ? Date::excelToDateTimeObject($row['تاريخ الميلاد']) : null; // التحويل من Excel serial

        $studentService = new StudentService();

        // Get current user from request context if available
        $currentUser = $this->request['user'] ?? null;

        $student = $studentService->create([
            'identity' => $row['رقم الهوية'],
            'fName' => $row['الاسم'],
            'sName' => $row['اسم الأب'],
            'thName' => $row['اسم الجد'],
            'family' => $row['العائلة'],
            'dob' => $date,
            'guardian_id' => $row['رقم هوية ولي الأمر'],
            'mosque_id' => $this->request['mosque_id'],
            'halaqa_id' => $this->request['halaqa_id'],
        ], $currentUser);

        return $student;
    }
}
