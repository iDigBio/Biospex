<?php

namespace App\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;
use Storage;

class ProfilePresenter extends BasePresenter
{
    /**
     * Build link to avatar if it exists. Otherwise set default
     * @return string
     */
    public function avatar_small() {
        $attachment = $this->wrappedObject->avatar;

        return $attachment->exists('small') && Storage::disk('public')->exists($attachment->variantPath('small')) ?
            $attachment->url('small') : Storage::url('avatars/small/missing.png');
    }

    /**
     * Build link to avatar if it exists. Otherwise set default
     * @return string
     */
    public function avatar_medium() {
        $attachment = $this->wrappedObject->avatar;

        return $attachment->exists('medium') && Storage::disk('public')->exists($attachment->variantPath('medium')) ?
            $attachment->url('medium') : Storage::url('avatars/medium/missing.png');
    }
}