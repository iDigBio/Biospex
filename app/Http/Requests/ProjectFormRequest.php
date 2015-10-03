<?php

namespace App\Http\Requests;

class ProjectFormRequest extends Request
{
    public function authorize()
    {
        return \Sentry::check();
    }

    public function rules()
    {
        $rules = [
            'group_id'          => 'required|integer|min:1',
            'title'             => 'required|between:6,140|unique:projects,title,' . $this->route('projects'),
            'contact'           => 'required',
            'contact_email'     => 'required|min:4|max:32|email',
            'description_short' => 'required|between:6,140',
            'description_long'  => 'required',
            'keywords'          => 'required',
            'banner'            => 'image|image_size:>=1200,>=300',
            'logo'              => 'image|image_size:<=300,<=200'
        ];

        foreach ($this->request->get('actor') as $key => $val) {
            $rules['actor.'.$key] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [];
        foreach ($this->request->get('actor') as $key => $val) {
            $messages['actor.'.$key.'.required'] = 'Workflow Actor is required.';
        }

        return $messages;
    }
}
