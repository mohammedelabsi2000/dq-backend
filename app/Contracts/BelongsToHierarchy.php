<?php

namespace App\Contracts;

interface BelongsToHierarchy
{
    // كل موديل يرجع هرميته من الأعلى للأسفل
    // مثال: Center يرجع [Branch, Region, Center]
    public function getHierarchyIds(): array;
    
    public function getHierarchyData();
}
