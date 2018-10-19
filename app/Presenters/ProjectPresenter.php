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
    public function logoStandardUrl() {
        $logo = $this->model->logo;

        return $this->variantExists($logo, 'standard') ?
            $logo->url('standard') : Storage::url('logos/standard/project.png');
    }

    /**
     * Build link to logo avatar.
     *
     * @return string
     */
    public function logoThumbUrl() {
        $logo = $this->model->logo;

        return $this->variantExists($logo,'thumb') ?
            $logo->url('thumb') : Storage::url('logos/thumb/project.png');
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

    /**
     * Return twitter with font awesome button
     * @return string
     */
    public function organizationIcon()
    {
        return $this->model->organization_website === null ? ''
            : '<a href="'. $this->model->organization_website .'"><i class="fas fa-building"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return twitter with font awesome button
     * @return string
     */
    public function twitterIcon()
    {
        return $this->model->twitter === null ? ''
            : '<a href="'. $this->model->twitter .'"><i class="fab fa-twitter"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return facebook with Icon awesome button
     * @return string
     */
    public function facebookIcon()
    {
        return $this->model->facebook === null ? ''
            : '<a href="'. $this->model->facebook .'"><i class="fab fa-facebook"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return facebook with Icon awesome button
     * @return string
     */
    public function contactEmailIcon()
    {
        return $this->model->contact_email === null ? ''
            : '<a href="mailto:'.$this->model->contact_email.'"><i class="far fa-envelope"></i> <span class="d-none text d-sm-inline"></span></a>';
    }
}