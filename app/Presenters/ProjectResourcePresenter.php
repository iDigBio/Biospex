<?php

namespace App\Presenters;

use Storage;

class ProjectResourcePresenter extends Presenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function resource()
    {
        $name = $this->model->name;
        $description = $this->model->description;

        if ($this->model->type === 'File Download') {
            return '<a href="'.$this->model->download->url().'" target="_blank" data-hover="tooltip" title="'.$description.'">
            <i class="fas fa-file"></i> '.$name.'</a>';
        }

        return '<a href="'.$name.'" target="_blank" data-hover="tooltip" title="'.$description.'">
            <i class="fas fa-link"></i> '.$name.'</a>';
    }
}