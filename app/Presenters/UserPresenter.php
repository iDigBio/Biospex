<?php

namespace App\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;

class UserPresenter extends BasePresenter
{
    public function full_name_or_email()
    {
        $firstName = $this->wrappedObject->profile->first_name;
        $lastName = $this->wrappedObject->profile->last_name;
        $email = $this->wrappedObject->email;

        $isNull = null === $firstName || null === $lastName ? true : false;

        return $isNull ? $email : $firstName . ' ' . $lastName;
    }
}