<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

class EditUserFormRequest extends Request
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
        return [
            'first_name'            => 'required',
            'last_name'             => 'required',
            'email'                 => 'required|min:4|max:32|email|unique:users,email,' . $this->route('users'),
        ];

    }
}
