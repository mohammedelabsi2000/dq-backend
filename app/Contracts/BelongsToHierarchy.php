<?php

namespace App\Contracts;

interface BelongsToHierarchy
{
    // كل موديل يرجع هرميته من الأعلى للأسفل
    // مثال: Center يرجع [Branch, Region, Center]
    public function getHierarchyIds(): array;
<<<<<<< HEAD
=======
    
>>>>>>> 98396415c3e71c9054eb8eada878cf2a0a07e54f
}
