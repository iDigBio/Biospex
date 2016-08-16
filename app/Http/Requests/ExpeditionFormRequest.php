<?php

namespace App\Http\Requests;

use Config;
use Illuminate\Support\Facades\Auth;

class ExpeditionFormRequest extends Request
{
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
        $rules = [
            'title' => 'required|between:6,140|unique:expeditions,title,' . $this->route('expeditions'),
            'description' => 'required|between:6,140',
            'keywords' => 'required',
            'subjectCount' => 'integer|max:' . Config::get('config.expedition_size'),
            'workflow' => 'integer|required_with:current_workflow:same:current_workflow'
        ];

        return $rules;
    }

    /**
     * Alter group form input before validation.
     *
     * @return array
     */
    public function alterInput()
    {
        $input = $this->all();
        $input['title'] = trim($input['title']);
        $this->replace($input);

        return $this->all();
    }
}
