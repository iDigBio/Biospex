<?php

namespace App\Presenters;

use Storage;

class ExpeditionPresenter extends Presenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function logoUrl() {
        $logo = $this->model->logo;

        return $this->variantExists($logo) ?
            $logo->url() : Storage::url('images/placeholders/card-image-place-holder02.jpg');
    }
}