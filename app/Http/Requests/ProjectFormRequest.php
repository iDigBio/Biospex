<?php

namespace Biospex\Http\Requests;

use Illuminate\Support\Facades\Auth;

class ProjectFormRequest extends Request
{
    public function authorize()
    {
        return Auth::check();
    }

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
            'banner'            => 'image|image_size:>=1200,>=300',
            'logo'              => 'image|image_size:<=300,<=200',
        ];

        return $rules;
    }
}
