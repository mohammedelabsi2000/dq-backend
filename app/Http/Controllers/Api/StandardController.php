<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class StandardController extends Controller
{
    public function circles()
    {
        $circles = app(\App\Services\AreaService::class)->getCircles();
        return $this->success($circles, 'success', 200);
    }

    public function courses()
    {
        $circleId = request()->query('circle_id');
        $gender = request()->query('gender');
        $courses = app(\App\Services\AreaService::class)->getCourses($circleId, $gender);
        return $this->success($courses, 'success', 200);
    }
}
