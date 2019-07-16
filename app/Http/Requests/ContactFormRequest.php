<?php

namespace App\Http\Requests;

class ContactFormRequest extends Request
{
    /**
     * Let any user submit form.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'                 => 'required',
            'email'                => 'required|min:4|max:32|email',
            'message'              => 'required',
            'g-recaptcha-response' => 'required|captcha',
        ];
    }
}
