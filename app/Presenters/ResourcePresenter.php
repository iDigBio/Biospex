<?php

namespace App\Presenters;

class ResourcePresenter extends Presenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function documentUrl()
    {
        $document = $this->model->document;

        if ($this->variantExists($document)) {
            return '<a href="'.$document->url().'" target="_blank"><i class="fas fa-file"></i> '.$document->originalFilename().'</a>';
        }

        return '';
    }

}