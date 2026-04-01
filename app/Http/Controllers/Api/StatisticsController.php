<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;

class StatisticsController extends Controller
{
    public function __construct(private StatisticsService $statisticsService)
    {
    }

    public function index()
    {
        return $this->success($this->statisticsService->getStatistics());
    }
}