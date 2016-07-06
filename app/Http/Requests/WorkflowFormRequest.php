<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class WorkflowFormRequest extends Request
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
            'title' => 'required|between:3,60|unique:faq_categories,name,' . $this->route('workflows'),
            'actors.*.id' => 'required'
        ];
    }
}
