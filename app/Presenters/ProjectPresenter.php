<?php

namespace App\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;
use Storage;

class ProjectPresenter extends BasePresenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function logo_url() {
        $attachment = $this->wrappedObject->logo;

        return $attachment->exists() ? $attachment->url() : Storage::url('logos/original/missing.png');
    }

    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function logo_thumb_url() {
        $attachment = $this->wrappedObject->logo;

        return $attachment->exists('thumb') ? $attachment->url('thumb') : Storage::url('logos/thumb/missing.png');
    }

    /**
     * Build link to logo avatar.
     *
     * @return string
     */
    public function logo_avatar_url() {
        $attachment = $this->wrappedObject->logo;

        return $attachment->exists('avatar') ? $attachment->url('avatar') : Storage::url('logos/avatar/missing.png');
    }

    /**
     * Build link to banner thumb.
     *
     * @return string
     */
    public function banner_url() {
        $attachment = $this->wrappedObject->banner;

        return $attachment->exists() ? $attachment->url() : Storage::url('banners/original/missing.png');
    }

    /**
     * Build link to banner thumb.
     *
     * @return string
     */
    public function banner_thumb_url() {
        $attachment = $this->wrappedObject->banner;

        return $attachment->exists('thumb') ? $attachment->url('thumb') : Storage::url('banners/thumb/missing.png');
    }

    /**
     * Build link to banner carousel. Not in use yet!!
     *
     * @return string
     */
    public function banner_carousel_url() {
        $attachment = $this->wrappedObject->banner;

        return $attachment->exists('carousel') ? $attachment->url('carousel') : Storage::url('banners/carousel/missing.png');
    }
}