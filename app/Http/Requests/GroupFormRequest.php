<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

class GroupFormRequest extends Request
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
            'name'     => 'required|min:4|max:32|unique:groups,name,' . $this->request->get('id'),
        ];
    }

    /**
     * Change input before validation.
     */
    public function alterInput()
    {
        $this->replace(['name' => strtolower(str_replace(' ', '-', $this->get('name')))]);
        
        return $this->all();
    }
}
