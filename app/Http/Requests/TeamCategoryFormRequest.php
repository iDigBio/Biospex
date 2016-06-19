<?php

namespace App\Http\Requests;

class TeamCategoryFormRequest extends Request
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
            'name' => 'required|between:6,50|unique:team_categories,name,' . $this->route('categories'),
        ];
    }

    /**
     * Override parent alterInput.
     *
     * @return array
     */
    public function alterInput()
    {
        $this->replace(['name' => strtolower(str_replace(' ', '-', $this->get('name')))]);

        return $this->all();
    }
}
