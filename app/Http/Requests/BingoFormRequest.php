<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class BingoFormRequest extends FormRequest
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
            'project_id'   => 'required',
            'title'        => 'required|between:5,20|unique:bingos,title,'.$this->route('bingos'),
            'directions'   => 'required|between:10,200',
            'contact'      => 'required|email',
            'words.*.word' => 'max:30',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'words.*.word.max' => 'Word is 20 character max',
        ];
    }
}
