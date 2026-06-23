<?php

namespace App\Filters\Traits;

use App\Enums\HalaqaReferenceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait CommonFilters
{
    /**
     * Apply common location-based filters (branch, region, center, mosque)
     */
    protected function applyLocationFilters(Builder|QueryBuilder $query, $request): Builder|QueryBuilder
    {
        return $query
            ->when($request->filled('branch_id'), fn($q) => $this->filterByBranch($q))
            ->when($request->filled('region_id'), fn($q) => $this->filterByRegion($q))
            ->when($request->filled('center_id'), fn($q) => $this->filterByCenter($q))
            ->when($request->filled('mosque_id'), fn($q) => $this->filterByMosque($q));
    }

    /**
     * Helper method for center/region morph patterns
     */
    protected function applyCenterRegionMorphFilter($query, callable $centerCallback, callable $regionCallback): Builder|QueryBuilder
    {
        return $query->where(function ($q) use ($centerCallback, $regionCallback) {
            $q->whereHasMorph('reference', HalaqaReferenceType::Center->code(), $centerCallback)
                ->orWhereHasMorph('reference', HalaqaReferenceType::Region->code(), $regionCallback);
        });
    }

    /**
     * Helper method for center morph only
     */
    protected function applyCenterMorphFilter($query, callable $centerCallback): Builder|QueryBuilder
    {
        return $query->whereHasMorph('reference', [HalaqaReferenceType::Center->code()], $centerCallback);
    }

    /**
     * Helper method for region morph only
     */
    protected function applyRegionMorphFilter($query, callable $regionCallback): Builder|QueryBuilder
    {
        return $query->whereHasMorph('reference', [HalaqaReferenceType::Region->code()], $regionCallback);
    }

    /**
     * Helper method for region-branch filter pattern
     */
    protected function applyRegionBranchFilter($query, int $branchId): Builder|QueryBuilder
    {
        return $query->whereHas('region', fn($q) => $q->where('branch_id', $branchId));
    }

    /**
     * Filter by branch_id - works for different entity types
     */
    protected function filterByBranch(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        $branchId = $this->request->integer('branch_id');

        // For entities that have direct region relationship
        if (method_exists($query->getModel(), 'region')) {
            return $this->applyRegionBranchFilter($query, $branchId);
        }

        // For students (complex logic with halaqas and mosques)
        if ($query->getModel()->getMorphClass() === 'student') {
            return $query->where(function ($q) use ($branchId) {
                $q->whereHas('halaqas', function ($hq) use ($branchId) {
                    $hq->where(function ($hqQuery) use ($branchId) {
                        $this->applyCenterRegionMorphFilter(
                            $hqQuery,
                            fn($centerQuery) => $this->applyRegionBranchFilter($centerQuery, $branchId),
                            fn($regionQuery) => $regionQuery->where('branch_id', $branchId)
                        );
                    });
                })
                    ->orWhereHas('mosque', function ($mosqueQuery) use ($branchId) {
                        $this->applyRegionBranchFilter($mosqueQuery, $branchId);
                    });
            });
        }

        // For halaqas (morph relationship)
        if ($query->getModel()->getMorphClass() === 'halaqa') {
            return $this->applyCenterRegionMorphFilter(
                $query,
                fn($centerQuery) => $this->applyRegionBranchFilter($centerQuery, $branchId),
                fn($regionQuery) => $regionQuery->where('branch_id', $branchId)
            );
        }

        return $query;
    }

    /**
     * Filter by region_id
     */
    protected function filterByRegion(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        $regionId = $this->request->integer('region_id');

        // Direct region relationship
        if (method_exists($query->getModel(), 'region')) {
            /* if ($this->request->filled('exclude_halaqa_students')) {
                $branchId = $this->request->integer('branch_id');
                return $query->where('id', $regionId)->where('branch_id', $branchId);
            } */
            return $query->where('region_id', $regionId);
        }

        // For students
        if ($query->getModel()->getMorphClass() === 'student') {
            return $query->where(function ($q) use ($regionId) {
                $q->whereHas('mosque', fn($mosqueQuery) => $mosqueQuery->where('region_id', $regionId));
            });
        }

        // For halaqas
        if ($query->getModel()->getMorphClass() === 'halaqa') {
            return $this->applyCenterRegionMorphFilter(
                $query,
                fn($centerQuery) => $centerQuery->where('region_id', $regionId),
                fn($regionQuery) => $regionQuery->where('id', $regionId)
            );
        }

        return $query;
    }

    /**
     * Filter by center_id
     */
    protected function filterByCenter(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        $centerId = $this->request->integer('center_id');

        // Direct center relationship
        if (method_exists($query->getModel(), 'center') || $query->getModel()->getTable() === 'centers') {
            return $query->where('id', $centerId);
        }

        // For students
        if ($query->getModel()->getMorphClass() === 'student') {
            return $query->where(function ($q) use ($centerId) {
                $q->whereHas('halaqas', function ($hq) use ($centerId) {
                    $this->applyCenterMorphFilter($hq, fn($centerQuery) => $centerQuery->where('id', $centerId));
                })
                    ->orWhereHas('mosque', function ($mosqueQuery) use ($centerId) {
                        $mosqueQuery->whereHas('centers', function ($centerQuery) use ($centerId) {
                            $centerQuery->where('id', $centerId);
                        });
                    });
            });
        }

        // For halaqas
        if ($query->getModel()->getMorphClass() === 'halaqa') {
            return $this->applyCenterMorphFilter($query, fn($query) => $query->where('id', $centerId));
        }

        return $query;
    }

    /**
     * Filter by mosque_id
     */
    protected function filterByMosque(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        $mosqueId = $this->request->integer('mosque_id');

        // Direct mosque relationship
        if (method_exists($query->getModel(), 'mosque')) {
            return $query->where('mosque_id', $mosqueId);
        }

        return $query;
    }
}
