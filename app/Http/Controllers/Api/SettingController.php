<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Models\Setting;
use App\Models\SettingLog;
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

        return $this->success($this->currentSettings(), 'إعدادات الاعتماد التلقائي');
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
            Setting::setValue($key, $value);
        }

        return $this->success($this->currentSettings(), 'تم تحديث الإعدادات بنجاح');
    }

    public function history(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('settings.show', 'sanctum')) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        $data = $request->validate([
            'key' => 'required|in:' . implode(',', self::APPROVAL_KEYS),
        ]);

        $logs = SettingLog::where('key', $data['key'])
            ->orderByDesc('start_dt')
            ->get(['id', 'key', 'start_dt', 'end_dt']);

        return $this->success($logs, 'سجل حركات الإعداد');
    }

    private function currentSettings()
    {
        $settings = Setting::whereIn('key', self::APPROVAL_KEYS)->get()->keyBy('key');

        return collect(self::APPROVAL_KEYS)->mapWithKeys(function ($key) use ($settings) {
            $setting = $settings->get($key);

            return [$key => [
                'value'     => $setting ? (bool) $setting->value : false,
                'opened_at' => $setting?->opened_at?->format('Y-m-d H:i'),
                'closed_at' => $setting?->closed_at?->format('Y-m-d H:i'),
            ]];
        });
    }
}
