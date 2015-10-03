<?php namespace App\Form\ChangePassword;

use App\Form\Form;
use App\Repositories\Contracts\User;

class ChangePasswordForm extends Form
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
    public function change(array $input)
    {
        if (! $this->valid($input)) {
            return false;
        }

        return $this->repo->changePassword($input);
    }
}
