<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FileUploadNameValidation implements Rule
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
     *
     * @param string $attribute field name
     * @param mixed $value field value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/^[\w-.]+$/', $value->getClientOriginalName()) === 1 ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'File name can only contain alphanumeric, underscores, or dashes';
    }
}
