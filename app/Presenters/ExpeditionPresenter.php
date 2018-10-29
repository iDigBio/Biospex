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
            $logo->url() : Storage::disk('public')::url('logos/original/missing.png');
    }
}