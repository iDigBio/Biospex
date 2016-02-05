<?php

namespace Biospex\Http\Requests;

class ImportFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Sentry::check();
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

        return;
    }

    public function inputChange()
    {
        $input = $this->all();

        // Alter record set if available
        if (isset($input['recordset'])) {
            $input['recordset'] = strstr($input['recordset'], '/') ?
                trim(strrchr($input['recordset'], "/"), "/") : trim($input['recordset']);
        }

        $this->replace($input);

        return $this->all();
    }
}
