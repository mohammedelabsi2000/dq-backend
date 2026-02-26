<?php
namespace App\Helpers;

use App\Models\Constant;
use App\Models\ConstantType;
use Illuminate\Support\Facades\Cache;

class ConstantHelper
{
    /**
     * جلب جميع الـ constants المرتبطة بنوع معين
     *
     * @param string $typeName اسم النوع في جدول constant_types
     * @return array
     */
    public static function getConstantsByType(string $typeName): array
    {
        // احصل على ID النوع من cache أو DB
        $typeId = Cache::rememberForever("constant_type_id_{$typeName}", function () use ($typeName) {
            return ConstantType::where('name', $typeName)->value('id');
        });

        if (!$typeId) {
            return [];
        }

        // جلب الـ constants من cache أو DB
        return Cache::rememberForever("constants_for_type_{$typeId}", function () use ($typeId) {
            return Constant::where('constant_type_id', $typeId)
                ->where('is_active', true)
                ->get()
                ->toArray();
        });
    }

    /**
     * جلب IDs فقط
     */
    public static function getConstantIdsByType(string $typeName): array
    {
        return array_column(self::getConstantsByType($typeName), 'id');
    }
}