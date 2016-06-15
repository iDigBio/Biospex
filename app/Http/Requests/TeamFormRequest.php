<?php

namespace App\Http\Requests;


class TeamFormRequest extends Request
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
            'faq_category_id' => 'required',
            'first_name'      => 'required',
            'last_name'       => 'required',
            'email'           => 'required|min:4|max:32|email|unique:teams',
            'institution'     => 'required|min:4|max:60'
        ];
    }
}
