<?php

namespace App\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;

class GroupPresenter extends BasePresenter
{
    /**
     * Build link to avatar if it exists. Otherwise set default
     * @return string
     */
    public function avatar_small() {
        $attachment = $this->wrappedObject->avatar;

        return $attachment->exists('small') ? $attachment->url('small') : url('avatars/small/missing.png');
    }

    /**
     * Build link to avatar if it exists. Otherwise set default
     * @return string
     */
    public function avatar_medium() {
        $attachment = $this->wrappedObject->avatar;

        return $attachment->exists('medium') ? $attachment->url('medium') : url('avatars/medium/missing.png');
    }
}