<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,

            // نطاق الـ DQ (كما هي مخزنة)
            'dq_range_from' => $this->DQ_range_from,
            'dq_range_to' => $this->DQ_range_to,

            // نطاق الـ DQ كأرقام (للعمليات الحسابية)
            'dq_range' => [
                'from' => $this->getNumericValue($this->DQ_range_from),
                'to' => $this->getNumericValue($this->DQ_range_to),
                'from_formatted' => $this->DQ_range_from,
                'to_formatted' => $this->DQ_range_to,
            ],

            // معلومات إضافية عن النطاق
            'range_info' => [
                'type' => $this->getRangeType(),
                'description' => $this->getRangeDescription(),
                'is_valid' => $this->isValidRange(),
            ],

            // إحصائيات (إذا كانت محملة)
            'students_count' => $this->when($this->students_count !== null, $this->students_count),
            'subjects_count' => $this->when($this->subjects_count !== null, $this->subjects_count),
        ];
    }

    /**
     * تحويل القيمة إلى رقم
     */
    private function getNumericValue($value): ?float
    {
        // إزالة أي رموز غير رقمية
        $numeric = preg_replace('/[^0-9.]/', '', $value);

        return is_numeric($numeric) ? (float) $numeric : null;
    }

    /**
     * تحديد نوع النطاق
     */
    private function getRangeType(): string
    {
        $from = $this->getNumericValue($this->DQ_range_from);
        $to = $this->getNumericValue($this->DQ_range_to);

        if ($from === null || $to === null) {
            return 'غير محدد';
        }

        if ($to - $from <= 10) {
            return 'ضيق';
        } elseif ($to - $from <= 30) {
            return 'متوسط';
        } else {
            return 'واسع';
        }
    }

    /**
     * وصف النطاق
     */
    private function getRangeDescription(): string
    {
        return "من {$this->DQ_range_from} إلى {$this->DQ_range_to}";
    }

    /**
     * التحقق من صحة النطاق
     */
    private function isValidRange(): bool
    {
        $from = $this->getNumericValue($this->DQ_range_from);
        $to = $this->getNumericValue($this->DQ_range_to);

        return $from !== null && $to !== null && $from <= $to;
    }
}