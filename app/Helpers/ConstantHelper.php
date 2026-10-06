<?php

namespace App\Helpers;

use App\Models\Constant;
use App\Models\ConstantType;

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
        $typeId = ConstantType::where('name', $typeName)->value('id');

        if (!$typeId) {
            return [];
        }

        return Constant::where('constant_type_id', $typeId)
            ->where('is_active', true)
            ->get()
            ->toArray();
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

    
    public static function getConstantKeysByType(string $typeName): array
    {
        return array_column(self::getConstantsByType($typeName), 'const_key');
    }

    /**
     * Get a constant ID by type name and constant name.
     * 
     * @param string $typeName
     * @param string $constantName
     * @return int|null
     */
    public static function getConstantIdByName(string $typeName, string $constantName): ?int
    {
        $constants = self::getConstantsByType($typeName);

        foreach ($constants as $constant) {
            if ($constant['name'] === $constantName) {
                return $constant['id'];
            }
        }

        return null;
    }
    
    public static function getConstantIdByKey(string $typeName, string $constantKey): ?int
    {
        $constants = self::getConstantsByType($typeName);

        foreach ($constants as $constant) {
            if ($constant['const_key'] === $constantKey) {
                return $constant['id'];
            }
        }

        return null;
    }
}
