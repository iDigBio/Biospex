<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class EventFormRequest extends FormRequest
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
        $rules = [
            'project_id' => 'required',
            'title' => 'required|between:6,140|unique:expeditions,title,' . $this->route('expeditions'),
            'description' => 'required|between:6,140',
            'contact' => 'required',
            'contact_email' => 'required|email',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ];

        return $rules;
    }
}
