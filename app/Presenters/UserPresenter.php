<?php

namespace App\Presenters;

class UserPresenter extends Presenter
{
    public function fullNameOrEmail()
    {
        $firstName = $this->model->profile()->first_name;
        $lastName = $this->model->profile()->last_name;
        $email = $this->model->email;

        $isNull = null === $firstName || null === $lastName ? true : false;

        return $isNull ? $email : $firstName . ' ' . $lastName;
    }
}