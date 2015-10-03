<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class EditUserFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = \Sentry::getUser();
        return $user->isSuperUser() ? true : $user->id == $this->route('users') ? true : false;
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
