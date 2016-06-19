<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

class ImportFormRequest extends Request
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
        switch ($this->get('method')) {
            case 'darwinCoreFileImport':
                return ['core' => 'required|mimes:zip'];
            case 'darwinCoreUrlImport':
                return ['core-url' => 'required|url'];
            case 'nfnTranscriptionImport':
                return ['transcription' => 'required|mimes:txt'];
            case 'recordSetImport':
                return ['recordset' => 'required|alpha_dash'];
            default:break;
        }
    }

    public function alterInput()
    {
        $replace = '';
        // Alter record set if available
        if ($this->input('recordset') !== null) {
            $replace = strstr($this->input('recordset'), '/') ?
                trim(strrchr($this->input('recordset'), '/'), '/') : trim($this->input('recordset'));
        }

        $this->replace(['recordset' => $replace]);

        return $this->all();
    }
}
