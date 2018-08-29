<?php

namespace App\Presenters;

class ResourcePresenter extends Presenter
{
    /**
     * Build link to document if it exists.
     *
     * @return string
     */
    public function documentUrl() {
        $id = $this->model->id;
        $document = $this->model->document;

        return $this->variantExists($document) ?
            link_to_route('web.resources.download', $document->originalFilename(), [$id]) : '';
    }
}