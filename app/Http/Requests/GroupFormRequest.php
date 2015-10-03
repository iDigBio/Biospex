<?php

namespace App\Http\Requests;

class GroupFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Sentry::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'     => 'required|min:4|max:32|unique:groups,name,' . $this->route('groups'),
        ];
    }
}
