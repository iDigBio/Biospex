<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

class InviteFormRequest extends Request
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
        return ['invites.*.email' => 'email'];
    }


    public function messages()
    {
        return [
            'invites.*.email' => 'Please enter valid email addresses'
        ];
    }

}
