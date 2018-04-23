<?php

namespace App\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;

class ProjectResourcePresenter extends BasePresenter
{
    /**
     * Build link to document if it exists.
     *
     * @return string
     */
    public function document_url() {
        $id = $this->wrappedObject->id;
        $attachment = $this->wrappedObject->download;

        return $attachment->exists() ?
            link_to_route('web.resources.download', $attachment->originalFilename(), [$id]) : '';
    }
}