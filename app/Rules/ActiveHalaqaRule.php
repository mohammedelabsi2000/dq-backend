<?php

namespace App\Rules;

use App\Models\Halaqa;
use Illuminate\Contracts\Validation\Rule;

class ActiveHalaqaRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $halaqa = Halaqa::find($value);

        return $halaqa && $halaqa->is_active;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'الحلقة المحددة غير فعالة، لا يمكن التنسيب إليها.';
    }
}
