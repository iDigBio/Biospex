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
     * Validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|min:4|max:32|not_in:'. config('config.admin_group') . '|unique:groups,title,' . $this->route('groups'),
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
        $input['title'] = $this->route('groups') === config('config.admin_group_id') ? config('config.admin_group_id') : trim($input['title']);
        $input['user_id'] = $input['owner'];
        $this->replace($input);

        return $this->all();
    }
}
