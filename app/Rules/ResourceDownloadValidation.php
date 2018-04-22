<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ResourceDownloadValidation implements Rule
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
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $parts = explode('.', $attribute);
        $resources = request()->get('resources');

        if ($resources[$parts[1]]['type'] !== 'File Download')
        {
            return true;
        }

        $fileUpload = request()->hasFile('resources.' . $parts[1] . '.download');
        $fileExists = isset($resources[$parts[1]]['download_file_name']) &&  ! empty($resources[$parts[1]]['download_file_name']) ? true : false;
        $fileName = $fileExists ? preg_match('/^[\w-.]+$/', $resources[$parts[1]]['download_file_name']) : false;

        return $fileUpload || $fileExists || $fileName;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Must upload file.';
    }
}
