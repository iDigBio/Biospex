<?php

namespace App\Presenters;

use Storage;

class ProjectPresenter extends Presenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function logoUrl() {
        $logo = $this->model->logo;

        return $this->variantExists($logo) ?
            $logo->url() : Storage::url('logos/original/missing.png');
    }

    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function logoThumbUrl() {
        $logo = $this->model->logo;

        return $this->variantExists($logo, 'thumb') ?
            $logo->url('thumb') : Storage::url('logos/thumb/missing.png');
    }

    /**
     * Build link to logo avatar.
     *
     * @return string
     */
    public function logoAvatarUrl() {
        $logo = $this->model->logo;

        return $this->variantExists($logo,'avatar') ?
            $logo->url('avatar') : Storage::url('logos/avatar/missing.png');
    }

    /**
     * Build link to banner thumb.
     *
     * @return string
     */
    public function bannerUrl() {
        $banner = $this->model->banner;

        return $this->variantExists($banner) ?
                $banner->url() : Storage::url('banners/original/missing.png');
    }

    /**
     * Build link to banner thumb.
     *
     * @return string
     */
    public function bannerThumbUrl() {
        $banner = $this->model->banner;

        return $this->variantExists($banner, 'thumb') ?
                $banner->url('thumb') : Storage::url('banners/thumb/missing.png');
    }

    /**
     * Build link to banner carousel. Not in use yet!!
     *
     * @return string
     */
    public function bannerCarouselUrl() {
        $banner = $this->model->banner;

        return $this->variantExists($banner, 'carousel') ?
            $banner->url('carousel') : Storage::url('banners/carousel/missing.png');
    }
}