<?php

namespace App\Http\Requests;

use App\Rules\FileUploadNameValidation;
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
            'logo'                    => [
                'image',
                new FileUploadNameValidation(),
            ],
            'subjectCount' => 'integer|max:' . Config::get('config.expedition_size'),
            'panoptes_workflow_id' => 'integer|nullable|required_with:current_panoptes_workflow_id:same:current_panoptes_workflow_id'
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
