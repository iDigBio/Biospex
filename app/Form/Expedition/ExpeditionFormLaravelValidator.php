<?php namespace App\Form\Expedition;

use App\Validation\AbstractLaravelValidator;

class ExpeditionFormLaravelValidator extends AbstractLaravelValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'title' => 'required',
        'description' => 'required',
    );
}
