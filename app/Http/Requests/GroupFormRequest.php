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
            'name' => 'required|min:4|max:32|not_in:'. env('ADMIN_GROUP') . '|unique:groups,name,' . $this->route('groups'),
            'user_id' => 'required'
        ];
    }

    /**
     * Alter group form input before validation.
     * 
     * @return array
     */
    public function alterInput()
    {
        $input = $this->all();
        $input['name'] = $this->route('groups') === env('ADMIN_GROUP_ID') ? env('ADMIN_GROUP') : $input['name'];
        $input['user_id'] = $input['owner'];
        $this->replace($input);

        return $this->all();
    }
}
