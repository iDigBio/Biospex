<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventJoinRequest extends FormRequest
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
            'group_id' => 'required',
            'nfn_user' => 'required|between:3,30'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'group_id.required'        => 'Group Id missing',
            'nfn_user.required' => 'Notes from Nature username required',
            'nfn_user.between' => 'Username must be between 3-30 characters',
        ];
    }
}
