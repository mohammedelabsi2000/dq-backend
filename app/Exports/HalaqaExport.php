<?php

namespace App\Exports;

use App\Models\Halaqa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HalaqaExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Halaqa::query()
            ->with([
                'type',
                'lastStatus.statusType',
                'lastStatus.sponsorshipType',
                'lastSupervisor.user',
                'studentEnrollments',
            ]);
    }

    public function headings(): array
    {
        return [
            'اسم الحلقة',
            'الفرع',
            'التبعية (محلية - مركز)',
            'المحلية',
            'المركز',
            'القسم',
            'النوع',
            'رقم هوية المعلم',
            'اسم المعلم',
            'عدد الطلاب',

            'الحالة',
            'نوع الكفالة',
            'الكفيل',
            'من تاريخ',
            'إلى تاريخ',
        ];
    }

    public function map($halaqa): array
    {
        $branchName = '';
        $dependency = 'مركز';
        $regionName = '';
        $centerName = '';
        if ($halaqa->reference) {
            if ($halaqa->reference instanceof \App\Models\Region) {
                $branchName = $halaqa->reference->branch?->name ?? '';
                $dependency = 'محلية';
                $regionName = $halaqa->reference->name;
            } elseif ($halaqa->reference instanceof \App\Models\Center) {
                $branchName = $halaqa->reference->region?->branch?->name ?? '';
                $regionName = $halaqa->reference->region?->name ?? '';
                $centerName = $halaqa->reference->name;
            }
        }

        return [
            $halaqa->name,
            $branchName,
            $dependency,
            $regionName,
            $centerName,
            $halaqa->gender,
            $halaqa->type?->name,
            $halaqa->lastSupervisor?->user?->identity,
            $halaqa->lastSupervisor?->user?->full_name ?? '',
            $halaqa->studentEnrollments->count() ?? 0,

            $halaqa->lastStatus?->statusType?->name,
            $halaqa->lastStatus?->sponsorshipType?->name,
            $halaqa->lastStatus?->sponsor_entity,
            $halaqa->lastStatus?->from_date,
            $halaqa->lastStatus?->to_date,
        ];
    }
}