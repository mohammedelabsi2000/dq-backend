<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponser;

    /**
     * المفاتيح المتاحة لإعدادات الاعتماد التلقائي
     */
    private const APPROVAL_KEYS = [
        Setting::AUTO_APPROVE_HALAQAS,
        Setting::AUTO_APPROVE_STUDENTS,
    ];

    public function index()
    {
        if (!auth()->user()->hasPermissionTo('settings.show', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $settings = collect(self::APPROVAL_KEYS)
            ->mapWithKeys(fn($key) => [$key => Setting::isEnabled($key)]);

        return $this->success($settings, 'إعدادات الاعتماد التلقائي');
    }

    public function update(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('settings.update', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $data = $request->validate([
            Setting::AUTO_APPROVE_HALAQAS  => 'sometimes|boolean',
            Setting::AUTO_APPROVE_STUDENTS => 'sometimes|boolean',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $settings = collect(self::APPROVAL_KEYS)
            ->mapWithKeys(fn($key) => [$key => Setting::isEnabled($key)]);

        return $this->success($settings, 'تم تحديث الإعدادات بنجاح');
    }
}
