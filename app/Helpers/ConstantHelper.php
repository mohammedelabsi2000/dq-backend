<?php
namespace App\Helpers;

use App\Models\Constant;
use App\Models\ConstantType;
use Illuminate\Support\Facades\Cache;

class ConstantHelper
{
    /**
     * Get all active constants for a given type name.
     * 
     * @param string $typeName
     * @return array
     */
    public static function getConstantsByType(string $typeName): array
    {
        // Get constant type ID from cache or DB
        $typeId = Cache::rememberForever("constant_type_id_{$typeName}", function () use ($typeName) {
            return ConstantType::where('name', $typeName)->value('id');
        });

        if (!$typeId) {
            return [];
        }

        // Return constants for the type from cache or DB
        return Cache::rememberForever("constants_for_type_{$typeId}", function () use ($typeId) {
            return Constant::where('constant_type_id', $typeId)
                ->where('is_active', true)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get an array of constant IDs for a given type name.
     * 
     * @param string $typeName
     * @return array
     */
    public static function getConstantIdsByType(string $typeName): array
    {
        return array_column(self::getConstantsByType($typeName), 'id');
    }
}