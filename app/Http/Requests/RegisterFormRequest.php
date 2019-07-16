<?php

namespace App\Http\Requests;

class RegisterFormRequest extends Request
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
        $table = (bool) $this->apiuser ? 'api_users' : 'users';

        return [
            'first_name'            => 'required',
            'last_name'             => 'required',
            'email'                 => 'required|min:4|max:32|email|unique:'.$table,
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
            'g-recaptcha-response'  => 'required|captcha',
        ];
    }
}
