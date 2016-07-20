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
            'team_category_id' => 'required',
            'first_name'       => 'required',
            'last_name'        => 'required',
            'email'            => 'required|min:4|max:32|email|unique:teams,email,' . $this->route('teams'),
            'institution'      => 'required|min:4|max:150'
        ];
    }
}
