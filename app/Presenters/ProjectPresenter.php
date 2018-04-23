<?php

namespace App\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;

class ProjectPresenter extends BasePresenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function logo_url() {
        $attachment = $this->wrappedObject->logo;

        return $attachment->exists() ? $attachment->url() : url('logos/original/missing.png');
    }

    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function logo_thumb_url() {
        $attachment = $this->wrappedObject->logo;

        return $attachment->exists('thumb') ? $attachment->url('thumb') : url('logos/thumb/missing.png');
    }

    /**
     * Build link to logo avatar.
     *
     * @return string
     */
    public function logo_avatar_url() {
        $attachment = $this->wrappedObject->logo;

        return $attachment->exists('avatar') ? $attachment->url('avatar') : url('logos/avatar/missing.png');
    }

    /**
     * Build link to banner thumb.
     *
     * @return string
     */
    public function banner_url() {
        $attachment = $this->wrappedObject->banner;

        return $attachment->exists() ? $attachment->url() : url('banners/original/missing.png');
    }

    /**
     * Build link to banner thumb.
     *
     * @return string
     */
    public function banner_thumb_url() {
        $attachment = $this->wrappedObject->banner;

        return $attachment->exists('thumb') ? $attachment->url('thumb') : url('banners/thumb/missing.png');
    }

    /**
     * Build link to banner carousel. Not in use yet!!
     *
     * @return string
     */
    public function banner_carousel_url() {
        $attachment = $this->wrappedObject->banner;

        return $attachment->exists('carousel') ? $attachment->url('carousel') : url('banners/carousel/missing.png');
    }
}