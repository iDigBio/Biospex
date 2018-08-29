<?php

namespace App\Presenters;

class ProjectResourcePresenter extends Presenter
{
    /**
     * Build link to document if it exists.
     *
     * @return string
     */
    public function documentUrl() {
        $id = $this->model->id;
        $download = $this->model->download;

        return $this->variantExists($download) ?
            link_to_route('web.resources.download', $download->originalFilename(), [$id]) : '';
    }
}