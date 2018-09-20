<?php

namespace App\Presenters;

class TeamPresenter extends Presenter
{
    /**
     * Get full name.
     *
     * @return string
     */
    public function fullName() {
        return $this->model->first_name . ' ' . $this->model->last_name;
    }
}