<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ResourceFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|between:3,60|unique:resources,title,' . $this->route('resources'),
            'description' => 'required',
            'document.file' => 'mimes:pdf'
        ];
    }
}
