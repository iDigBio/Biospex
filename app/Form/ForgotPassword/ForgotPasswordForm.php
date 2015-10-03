<?php namespace App\Form\ForgotPassword;

use App\Form\Form;
use App\Repositories\Contracts\User;

class ForgotPasswordForm extends Form
{
    public function __construct(User $user)
    {
        $this->repo = $user;
    }

    /**
     * Create a new user
     *
     * @return integer
     */
    public function forgot(array $input)
    {
        if (! $this->valid($input)) {
            return false;
        }

        return $this->repo->forgotPassword($input);
    }
}
