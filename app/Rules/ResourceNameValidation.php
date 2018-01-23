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

        if ($resources[$parts[1]]['type'] !== null && $value === null)
        {
            $this->message = trans('errors.resource_empty');

            return false;
        }

        if ($resources[$parts[1]]['type'] === 'Website URL' || $resources[$parts[1]]['type'] === 'Video URL')
        {
            $regex = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';

            $this->message = trans('errors.resource_url_required');

            return preg_match($regex, $value);
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
        return $this->message;
    }
}
