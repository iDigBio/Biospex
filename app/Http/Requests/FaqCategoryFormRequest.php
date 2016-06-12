<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

class FaqCategoryFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|between:6,20|unique:faq_categories,name,' . $this->route('faqs'),
        ];

        return $rules;
    }
}
