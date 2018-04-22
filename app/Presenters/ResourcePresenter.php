<?php

namespace App\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;

class ResourcePresenter extends BasePresenter
{
    public function document_url() {
        $id = $this->wrappedObject->id;
        $attachment = $this->wrappedObject->document;

        if ($attachment->exists()){
            return link_to_route('web.resources.download', $attachment->originalFilename(), [$id]);
        }

        return '';
    }
}