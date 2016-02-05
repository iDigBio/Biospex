<?php

namespace Biospex\Http\Requests;

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
        $rules = ['emails' => 'required'];

        if (empty($this->request->get('emails'))) {
            return $rules;
        }

        $emails = explode(',', $this->request->get('emails'));

        foreach ($emails as $key => $val) {
            $rules['emails.' . $key] = 'email';
        }

        return $rules;
    }

    public function messages()
    {
        if (empty($this->request->get('emails'))) {
            return ['required'];
        }

        $messages = [];
        $emails = explode(',', $this->request->get('emails'));
        foreach ($emails as $key => $val) {
            $messages['emails.' . $key . '.emails'] = 'Incorrect format for ' . trim($val);
        }

        return $messages;
    }
}
