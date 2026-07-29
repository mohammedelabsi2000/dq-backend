<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AreaService;

class AreaController extends Controller
{
    public function __construct(
        private readonly AreaService $areaService
    ) {}

    public function index()
    {
        try {
            $branches = $this->areaService->getBranches();
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 502);
        }

        return $this->success($branches, 'success');
    }

    public function subAreas(int $id)
    {
        try {
            $regions = $this->areaService->getBranchRegions($id);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 502);
        }

        return $this->success($regions, 'success');
    }
}
