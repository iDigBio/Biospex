<?php namespace App\Form\Expedition;

use App\Form\Form;
use App\Validation\ValidableInterface as Validator;
use App\Repositories\Contracts\Expedition as Expedition;

class ExpeditionForm extends Form
{
    public function __construct(Validator $validator, Expedition $expedition)
    {
        $this->validator = $validator;
        $this->repo = $expedition;
    }
}
