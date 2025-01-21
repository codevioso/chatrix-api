<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Enums\MediaType;

class MediaTypeRule implements Rule
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
        // Validate against MediaType enum
        return in_array($value, array_column(MediaType::cases(), 'value'));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a valid media type.';
    }
}
