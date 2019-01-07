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
    public function logoUrl()
    {
        $logo = $this->model->logo;

        return $this->variantExists($logo) ? $logo->url() : Storage::url('images/placeholders/missing.png');
    }

    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function logoStandardUrl()
    {
        $logo = $this->model->logo;

        return $this->variantExists($logo, 'standard') ? $logo->url('standard') : Storage::url('images/placeholders/project.png');
    }

    /**
     * Build link to logo avatar.
     *
     * @return string
     */
    public function logoThumbUrl()
    {
        $logo = $this->model->logo;

        return $this->variantExists($logo, 'thumb') ? $logo->url('thumb') : Storage::url('images/placeholders/project.png');
    }

    /**
     * Build link to banner thumb.
     *
     * @return string
     */
    public function bannerUrl()
    {
        $banner = $this->model->banner;

        return $this->variantExists($banner) ? $banner->url() : Storage::url('images/page-banners/banner-binoculars.jpg');
    }

    /**
     * Build link to banner thumb.
     *
     * @return string
     */
    public function bannerThumbUrl()
    {
        $banner = $this->model->banner;

        return $this->variantExists($banner, 'thumb') ? $banner->url('thumb') : Storage::url('banners/thumb/missing.png');
    }

    /**
     * Build link to banner carousel. Not in use yet!!
     *
     * @return string
     */
    public function bannerCarouselUrl()
    {
        $banner = $this->model->banner;

        return $this->variantExists($banner, 'carousel') ? $banner->url('carousel') : Storage::url('banners/carousel/missing.png');
    }

    /**
     * Return project home button
     *
     * @return string
     */
    public function projectPageIcon()
    {
        $route = route("projects.get.slug", [$this->model->slug]);

        return $this->model->slug == null ? '' : '<a href="'.$route.'" data-toggle="tooltip" title="Project Page"><i class="fas fa-project-diagram"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return project events small icon
     *
     * @return string
     */
    public function projectEventsIcon()
    {
        $route = route("projects.get.slug", [$this->model->slug]);

        return $this->model->events_count == null ? '' : '<a href="'.$route.'#events" data-toggle="tooltip" title="Events"><i class="far fa-calendar-times"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return project events large icon
     *
     * @return string
     */
    public function projectEventsIconLg()
    {
        $route = route("projects.get.slug", [$this->model->slug]);

        return $this->model->events_count == null ? '' : '<a href="'.$route.'#events" data-toggle="tooltip" title="Events"><i class="far fa-calendar-times fa-2x"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return organization icon
     *
     * @return string
     */
    public function organizationIcon()
    {
        return $this->model->organization_website == null ? '' : '<a href="'.$this->model->organization_website.'" target="_blank" data-toggle="tooltip" title="Organization"><i class="fas fa-building"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return twitter small icon
     *
     * @return string
     */
    public function twitterIcon()
    {
        return $this->model->twitter == null ? '' : '<a href="'.$this->model->twitter.'" target="_blank" data-toggle="tooltip" title="Twitter"><i class="fab fa-twitter"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return twitter large icon
     *
     * @return string
     */
    public function twitterIconLg()
    {
        return $this->model->twitter == null ? '' : '<a href="'.$this->model->twitter.'" target="_blank" data-toggle="tooltip" title="Twitter"><i class="fab fa-twitter fa-2x"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return facebook small icon
     *
     * @return string
     */
    public function facebookIcon()
    {
        return $this->model->facebook == null ? '' : '<a href="'.$this->model->facebook.'" target="_blank" data-toggle="tooltip" title="Facebook"><i class="fab fa-facebook"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return facebook large icon
     *
     * @return string
     */
    public function facebookIconLg()
    {
        return $this->model->facebook == null ? '' : '<a href="'.$this->model->facebook.'" target="_blank" data-toggle="tooltip" title="Facebook"><i class="fab fa-facebook fa-2x"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return blog small icon
     *
     * @return string
     */
    public function blogIcon()
    {
        return $this->model->blog_url == null ? '' : '<a href="'.$this->model->blog_url.'" target="_blank" data-toggle="tooltip" title="Blog"><i class="fab fa-blogger-b"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return blog large icon
     *
     * @return string
     */
    public function blogIconLg()
    {
        return $this->model->blog_url == null ? '' : '<a href="'.$this->model->blog_url.'" target="_blank" data-toggle="tooltip" title="Blog"><i class="fab fa-blogger-b fa-2x"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return contact small icon
     *
     * @return string
     */
    public function contactEmailIcon()
    {
        return $this->model->contact_email == null ? '' : '<a href="mailto:'.$this->model->contact_email.'" data-toggle="tooltip" title="Contact"><i class="far fa-envelope"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return contact large icon
     *
     * @return string
     */
    public function contactEmailIconLg()
    {
        return $this->model->contact_email == null ? '' : '<a href="mailto:'.$this->model->contact_email.'" data-toggle="tooltip" title="Contact"><i class="far fa-envelope fa-2x"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return expeditions icon on project home page.
     *
     * @return string
     */
    public function projectExpeditions()
    {
        return '<a href="#expeditions" data-toggle="tooltip" title="Expeditions"><i class="fas fa-binoculars fa-2x"></i></a>';
    }
}