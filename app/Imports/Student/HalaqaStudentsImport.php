<?php

namespace App\Imports\Student;

use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class HalaqaStudentsImport implements ToCollection, WithHeadingRow
{
    protected Request $request;
    protected StudentService $studentService;
    private array $map = [
        'رقم هوية الطالب' => 'identity',
        'رقم التواصل' => 'contact_number',
        'رقم هوية ولي الأمر' => 'guardian_id',
        'صلة القرابة' => 'guardian_type',
        'الاسم' => 'name',
        'م.' => 'index',
    ];
    protected array $failedRows = [];
    protected int $successCount = 0;
    protected int $totalCount = 0;

    public function __construct(
        protected $mosqueId,
        protected $halaqaId
    ) {
        $this->studentService = new StudentService();
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $this->totalCount = $collection->count();

        foreach ($collection as $index => $row) {
            $this->processRow(
                $row->toArray(),
                $index + 2
            );
        }
    }
    protected function processRow(array $row, int $excelRow): void
    {
        $data = $this->mapRow($row);

        $validator = Validator::make(
            $data,
            $this->rules(),
            $this->messages()
        );

        if ($validator->fails()) {
            $this->addFailedRow(
                $data,
                $excelRow,
                $validator->errors()->all()
            );
            return;
        }

        $data['mosque_id'] = $this->mosqueId;
        $data['halaqa_id'] = $this->halaqaId;

        try {
            $this->studentService->createOrUpdateStudent($data);

            $this->successCount++;
        } catch (\Throwable $e) {
            $this->addFailedRow(
                $data,
                $excelRow,
                [$e->getMessage()]
            );
        }
    }
    protected function mapRow(array $row): array
    {
        $newData = [];
        foreach ($row as $key => $value) {
            $newKey = $this->map[$key] ?? $key;
            $newData[$newKey] = $value;
        }
        return $newData;
    }

    protected function rules(): array
    {
        return [
            'identity' => [
                'required',
                'numeric',
                'digits:9',
            ],

            'contact_number' => [
                'nullable',
                'string',
            ],

            'guardian_id' => [
                'required',
                'numeric',
                'digits:9',
            ],

            'guardian_type' => [
                'nullable',
                'string',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'identity.required' => 'رقم هوية الطالب مطلوب.',
            'identity.numeric' => 'رقم هوية الطالب يجب أن يكون رقماً.',
            'identity.digits' => 'رقم هوية الطالب يجب أن يكون 9 أرقام.',

            'contact_number.string' => 'رقم التواصل يجب أن يكون نصاً.',

            'guardian_id.required' => 'رقم هوية ولي الأمر مطلوب.',
            'guardian_id.numeric' => 'رقم هوية ولي الأمر يجب أن يكون رقماً.',
            'guardian_id.digits' => 'رقم هوية ولي الأمر يجب أن يكون 9 أرقام.',

            'guardian_type.string' => 'صلة القرابة يجب أن تكون نصاً.',
        ];
    }

    protected function addFailedRow(
        array $data,
        int $excelRow,
        array $errors
    ): void {
        $newData['identity'] = $data['identity'];
        $newData['excel_row'] = $excelRow;
        $newData['error'] = implode(' | ', $errors);

        $this->failedRows[] = $newData;
    }

    public function getFailedRows(): array
    {
        return $this->failedRows;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailedCount(): int
    {
        return count($this->failedRows);
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
