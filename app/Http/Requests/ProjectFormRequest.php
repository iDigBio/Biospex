<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

class ProjectFormRequest extends Request
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
            'group_id'          => 'required|integer|min:1',
            'status'            => 'required',
            'title'             => 'required|between:6,140|unique:projects,title,' . $this->route('projects'),
            'contact'           => 'required',
            'contact_email'     => 'required|min:4|max:32|email',
            'contact_title'     => 'required',
            'description_short' => 'required|between:6,140',
            'description_long'  => 'required',
            'keywords'          => 'required',
            'workflow_id'       => 'required',
            'banner'            => 'image|image_size:>=1200,>=250',
            'logo'              => 'image|image_size:<=300,<=200',
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
