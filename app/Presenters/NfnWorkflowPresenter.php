<?php

namespace App\Presenters;

class NfnWorkflowPresenter extends Presenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function nfnUrl() {
        return $this->model->workflow === null ? '#'
            : '<a href="https://www.zooniverse.org/projects/zooniverse/notes-from-nature/classify?reload=1&workflow='.$this->model->workflow.'" target="_blank">
              <i class="far fa-keyboard"></i>'.__('Participate').'</a>';
    }
}