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
        /**
        [resources] => Array
        (
        [1] => Array
        (
        [download] => Illuminate\Http\UploadedFile Object
        (
        [test:Symfony\Component\HttpFoundation\File\UploadedFile:private] =>
        [originalName:Symfony\Component\HttpFoundation\File\UploadedFile:private] => 21_Cryptos Magazine January 2018-3.pdf
        [mimeType:Symfony\Component\HttpFoundation\File\UploadedFile:private] => application/pdf
        [size:Symfony\Component\HttpFoundation\File\UploadedFile:private] => 5375683
        [error:Symfony\Component\HttpFoundation\File\UploadedFile:private] => 0
        [hashName:protected] =>
        [pathName:SplFileInfo:private] => /tmp/php1hX8bG
        [fileName:SplFileInfo:private] => php1hX8bG
        )

        )

        )
         */
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
        $fileUpload = request()->hasFile('resources.'.$parts[1].'.download');

        if ($resources[$parts[1]]['type'] !== 'File Download'||
            $resources[$parts[1]]['type'] === 'delete' ||
            ! $fileUpload) {
            return true;
        }

        $file = request()->file('resources.'.$parts[1].'.download');
        $fileName = preg_match('/^[\w\-.]+$/', $file->getClientOriginalName()) === 1 ? true : false;

        return $fileName;
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return 'File required and name can only contain alphanumeric, dash, or underscore.';
    }
}
