<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mosque\StoreMosqueRequest;
use App\Http\Requests\Mosque\UpdateMosqueRequest;
use App\Http\Resources\MosqueResource;
use App\Http\Resources\CenterResource;
use App\Models\Mosque;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MosqueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Mosque::class);

        $query = Mosque::query()->visibleTo(auth()->user());

        if (auth()->user()->hasPermissionTo('mosques.restore')) {
            $query = $query->withTrashed();
        }

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        // فلترة حسب المنطقة
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        // فلترة حسب البرانش
        if ($request->filled('branch_id')) {
            $query->whereHas('region', function ($q) use ($request) {
                $q->where('branch_id', $request->integer('branch_id'));
            });
        }

        $mosques = $query->withCount('centers')->get();

        return $this->successWithPagination(
            MosqueResource::collection($mosques),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreMosqueRequest $request)
    {
        $center = null;
        $mosque = null;

        DB::transaction(function () use ($request, &$center, &$mosque) {
            $mosque = Mosque::create($request->validated());

            // Check if user wants to create a center with the same name
            if ($request->boolean('create_center')) {
                $centerData = [
                    'name' => $request->input('name'),
                    'region_id' => $request->input('region_id'),
                    'gender' => $request->user()->gender,
                    'mosque_id' => $mosque->id,
                ];
                $center = Center::create($centerData);
            }
        });

        if ($request->boolean('with_region') && $mosque) {
            $mosque->load('region.branch');
        }

        // Prepare response data
        $responseData = [
            'mosque' => new MosqueResource($mosque),
        ];

        if ($center) {
            if ($request->boolean('with_region')) {
                $center->load('mosque.region.branch');
            }
            $responseData['center'] = new CenterResource($center);
        }

        return $this->success(
            $responseData,
            $center ? 'تم إنشاء المسجد والمركز بنجاح' : 'تم إنشاء المسجد بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @param  Mosque  $mosque
     */
    public function show(Request $request, Mosque $mosque)
    {
        $this->authorize('view', $mosque);

        if ($request->boolean('with_region')) {
            $mosque->load('region.branch');
        }

        return $this->success(
            new MosqueResource($mosque),
            'بيانات المسجد'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateMosqueRequest  $request
     * @param  Mosque  $mosque
     */
    public function update(UpdateMosqueRequest $request, Mosque $mosque)
    {
        $this->authorize('update', $mosque);

        $center = null;

        DB::transaction(function () use ($request, $mosque, &$center) {
            // Store original values before update
            $originalName = $mosque->name;
            $mosque->update($request->validated());

            // Check if user wants to create a center with the same name
            if ($request->boolean('create_center')) {
                // Check if center already exists with same name, region, and mosque
                $centerName = $request->input('name', $mosque->name);
                $centerRegionId = $request->input('region_id', $mosque->region_id);

                $existingCenter = Center::where('name', $centerName)
                    ->where('region_id', $centerRegionId)
                    ->where('mosque_id', $mosque->id)
                    ->first();

                // Only create new center if none exists with same specifications
                if (!$existingCenter) {
                    $centerData = [
                        'name' => $centerName,
                        'region_id' => $centerRegionId,
                        'mosque_id' => $mosque->id,
                        // 'notes' => $request->input('notes', $mosque->notes),
                    ];
                    $center = Center::create($centerData);
                }
            }
        });

        if ($request->boolean('with_region')) {
            $mosque->load('region.branch');
        }

        // Prepare response data
        $responseData = [
            'mosque' => new MosqueResource($mosque),
        ];

        if ($center) {
            if ($request->boolean('with_region')) {
                $center->load('mosque.region.branch');
            }
            $responseData['center'] = new CenterResource($center);
        }

        // Prepare success message
        $message = 'تم تحديث بيانات المسجد بنجاح';
        if ($center) {
            $message = 'تم تحديث بيانات المسجد وإنشاء مركز جديد بنجاح';
        }

        return $this->success(
            $responseData,
            $message
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Mosque  $mosque
     */
    public function destroy(Mosque $mosque)
    {
        $this->authorize('delete', $mosque);

        // تحقق من وجود مراكز تابعة قبل الحذف
        if ($mosque->centers()->exists()) {
            return $this->error(
                'لا يمكن حذف المسجد لأنه يحتوي على مراكز تابعة',
                400
            );
        }

        $mosque->delete();

        return $this->success(
            null,
            'تم حذف المسجد بنجاح'
        );
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  Mosque  $mosque
     */
    public function restore(Mosque $mosque)
    {
        $this->authorize('restore', $mosque);
        $mosque->restore();

        return $this->success(
            new MosqueResource($mosque),
            'تم استعادة المسجد بنجاح'
        );
    }
}
