<?php

namespace App\Http\Requests;

class FaqCategoryFormRequest extends Request
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
        $rules = [
            'name' => 'required|between:3,60|unique:faq_categories,name,' . $this->route('categories')
        ];

        return $rules;
    }
}
