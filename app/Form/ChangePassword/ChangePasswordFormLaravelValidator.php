<?php namespace App\Form\ChangePassword;

use App\Validation\AbstractLaravelValidator;

class ChangePasswordFormLaravelValidator extends AbstractLaravelValidator
{
    /**
     * Validation rules
     *
     * @var Array 
     */
    protected $rules = array(
        'oldPassword' => 'required|min:6',
        'newPassword' => 'required|min:6|confirmed',
        'newPassword_confirmation' => 'required'
    );
}
