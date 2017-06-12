<?php

namespace App\Http\Requests;

class ActorFormRequest extends Request
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
            'title' => 'required|between:3,60|unique:actors,title,' . $this->route('actors'),
            'url' => 'required|active_url|max:255',
            'class' => 'required|between:3,30|unique:actors,class,' . $this->route('actors'),
            'contacts.*.email' => 'email'
        ];
    }

    public function messages()
    {
        return [
            'contacts.*.email' => 'Please enter valid email addresses'
        ];
    }

    public function alterInput()
    {
        $input = $this->all();
        $input['private'] = null === $this->input('private') ? 0 : 1;
        $this->replace($input);

        return $this->all();
    }
}
