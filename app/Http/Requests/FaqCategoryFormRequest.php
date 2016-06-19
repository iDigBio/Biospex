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
            'name' => 'required|between:6,20|unique:faq_categories,name,' . $this->route('categories')
        ];

        return $rules;
    }

    /**
     * Alter input before validation.
     */
    public function alterInput()
    {
        $this->replace(['name' => strtolower(str_replace(' ', '-', $this->input('name')))]);

        return $this->all();
    }
}
