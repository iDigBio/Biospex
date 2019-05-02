<?php

namespace App\Presenters;

class EventTeamPresenter extends Presenter
{

    /**
     * Return return copy icon.
     *
     * @return string
     */
    public function teamJoinUrlIcon()
    {
        return '<a href="#" class="btn btn-primary p-2 m-1 prevent-default"
                title="'.__('pages.copy_to_clipboard').'" 
                data-hover="tooltip"
                data-name="js-copy"
            data-value="'.route('front.events.signup', [$this->model->uuid]).'">
            <i class="fas fa-clipboard align-middle" aria-hidden="true"></i>
            <span class="align-middle">'.$this->model->title.'</span></a>';
    }
}