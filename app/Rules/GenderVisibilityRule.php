<?php

namespace App\Rules;

use App\Support\CurrentUserContext;
use Illuminate\Contracts\Validation\Rule;

class GenderVisibilityRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = app(CurrentUserContext::class)->user();

        if (!$user) {
            return false;
        }

        if ($user->can('gender_visibility')) {
            return true;
        }
        return $value == $user->gender->value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'لا يمكنك اختيار جنس مختلف عن جنس المستخدم.';
    }
}
