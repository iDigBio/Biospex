<?php

namespace App\Presenters;

use Storage;

class ProfilePresenter extends Presenter
{
    /**
     * Build link to avatar if it exists. Otherwise set default
     * @return string
     */
    public function avatarSmall() {
        $avatar= $this->model->avatar;

        return $this->variantExists($avatar, 'small') ?
            $avatar->url('small') : Storage::url('avatars/small/missing.png');
    }

    /**
     * Build link to avatar if it exists. Otherwise set default
     * @return string
     */
    public function avatarMedium() {
        $avatar = $this->model->avatar;

        return $this->variantExists($avatar, 'medium') ?
            $avatar->url('medium') : Storage::url('avatars/medium/missing.png');
    }
}