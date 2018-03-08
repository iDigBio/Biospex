<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ResourceNameValidation implements Rule
{
    /**
     * messages
     */
    public $message;


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
     * attribute = resource.*.name
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $parts = explode('.', $attribute);
        $resources = request()->get('resources');

        if ($resources[$parts[1]]['type'] === 'Website URL' || $resources[$parts[1]]['type'] === 'Video URL')
        {
            return filter_var($value, FILTER_VALIDATE_URL);
        }

        return true;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('messages.resource_url_required');
    }
}
