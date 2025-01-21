<?php

namespace App\Rules;

use App\Enums\RoomType;
use Illuminate\Contracts\Validation\Rule;

class RoomTypeRule implements Rule
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
        // Validate against RoomType enum
        return in_array($value, array_column(RoomType::cases(), 'value'));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a valid room type.';
    }
}
