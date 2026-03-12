<?php

use App\Models\Branch;
use App\Models\Center;
use App\Models\Region;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {

    $rtn = User::with('roles')->first();
    // $resource = Region::first();
    // $rtn = $resource->getHierarchyData();

    // الآن يمكنك عرض البيانات في الواجهة
    /* foreach ($hierarchyData as $level) {
        echo "{$level['model']}: {$level['name']}<br>";
    } */
    return response()->json(['message' => 'API is working!', 'data' => $rtn]);
});

