<?php

namespace App\Presenters;

class NfnWorkflowPresenter extends Presenter
{
    /**
     * Return nfn icon.
     *
     * @return string
     */
    public function nfnUrl()
    {
        return $this->model->workflow === null ? '#' :
            '<a href="https://www.zooniverse.org/projects/zooniverse/notes-from-nature/classify?reload=1&workflow='.$this->model->workflow.'" 
                data-toggle="tooltip" title="'.__('Participate').'" target="_blank"><i class="fas fa-keyboard"></i></a>';
    }

    /**
     * Return nfn lrg icon
     *
     * @return string
     */
    public function nfnUrlLrg()
    {
        return $this->model->workflow === null ? '#' :
            '<a href="https://www.zooniverse.org/projects/zooniverse/notes-from-nature/classify?reload=1&workflow='.$this->model->workflow.'" 
                data-toggle="tooltip" title="'.__('Participate').'" target="_blank"><i class="fas fa-keyboard fa-2x"></i></a>';
    }
}