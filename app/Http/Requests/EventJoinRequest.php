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
        return false;
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
            'nfn_user' => 'required|between:6,100'
        ];

        return $rules;
    }
}
