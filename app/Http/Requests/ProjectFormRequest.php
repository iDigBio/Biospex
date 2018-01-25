<?php

namespace App\Http\Requests;

use App\Rules\ResourceDownloadValidation;
use Illuminate\Support\Facades\Auth;
use App\Rules\ResourceNameValidation;

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
            'group_id'                => 'required|integer|min:1',
            'status'                  => 'required',
            'title'                   => 'required|between:6,140|unique:projects,title,'.$this->route('projects'),
            'contact'                 => 'required',
            'contact_email'           => 'required|min:4|max:32|email',
            'contact_title'           => 'required',
            'description_short'       => 'required|between:6,140',
            'description_long'        => 'required',
            'keywords'                => 'required',
            'workflow_id'             => 'required',
            'organization_website'    => 'nullable|url',
            'blog_url'                => 'nullable|url',
            'facebook'                => 'nullable|url',
            'twitter'                 => 'nullable|url',
            'banner'                  => 'image|dimensions:min_width=1200,min_height=250',
            'logo'                    => 'image|dimensions:max_width=300,max_height=200',
            'resources.*.type'        => [new ResourceDownloadValidation()],
            'resources.*.name'        => ['required_with:resources.*.type', new ResourceNameValidation()],
            'resources.*.description' => 'required_with:resources.*.type',
            'resources.*.download'    => 'mimes:txt,doc,csv,pdf',
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

    public function messages()
    {
        return [
            'resources.*.name.required_with'        => 'Type selected',
            'resources.*.description.required_with' => 'Type selected',
            'resources.*.download.required_if'      => 'Type selected',
            'resources.*.download.mimes'            => 'Accepted files: txt,doc,csv,pdf',
        ];
    }
}
