<?php

namespace App\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;
use Storage;

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

        return $attachment->exists()  && Storage::disk('public')->exists($attachment->variantPath()) ?
            link_to_route('web.resources.download', $attachment->originalFilename(), [$id]) : '';
    }
}