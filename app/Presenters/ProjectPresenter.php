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

        return $this->variantExists($logo) ?
            $logo->url() :
            Storage::url('images/placeholders/project.png');
    }

    /**
     * Build link to banner.
     *
     * @return string
     */
    public function bannerFileName()
    {
        return $this->model->banner_file ?? 'banner-trees.jpg';
    }

    /**
     * Build link to banner.
     *
     * @return string
     */
    public function bannerFileUrl()
    {
        $banner = $this->model->banner_file;

        return $banner === null ?
            Storage::url('images/habitat-banners/banner-trees.jpg') :
            Storage::url('images/habitat-banners/'.$banner);
    }

    /**
     * Build link to banner thumb.
     *
     * @return string
     */
    public function bannerThumbUrl()
    {
        $banner = $this->model->banner;

        return $this->variantExists($banner, 'thumb') ?
            $banner->url('thumb') :
            Storage::url('banners/thumb/missing.png');
    }

    /**
     * Return project home button
     *
     * @return string
     */
    public function projectPageIcon()
    {
        $route = route("front.projects.slug", [$this->model->slug]);

        return $this->model->slug == null ? '' :
            '<a href="'.$route.'" data-hover="tooltip" title="'.__('Project Public Page').'">
                <i class="fas fa-project-diagram"></i></a>';
    }

    /**
     * Return project home button
     *
     * @return string
     */
    public function projectPageIconLrg()
    {
        $route = route("front.projects.slug", [$this->model->slug]);

        return $this->model->slug == null ? '' :
            '<a href="'.$route.'" target="_blank" data-hover="tooltip" title="'.__('Project Public Page').'">
                <i class="fas fa-project-diagram fa-2x"></i></a>';
    }

    /**
     * Return project home button
     *
     * @return string
     */
    public function projectAdminIconLrg()
    {
        $route = route("admin.projects.show", [$this->model->id]);

        return $this->model->id == null ? '' :
            '<a href="'.$route.'" data-hover="tooltip" title="'.__('Show Project Admin Page').'">
                <i class="fas fa-project-diagram fa-2x"></i></a>';
    }

    /**
     * Return project events small icon
     *
     * @return string
     */
    public function projectEventsIcon()
    {
        $route = route("front.projects.slug", [$this->model->slug]);

        return $this->model->events_count == null ? '' :
            '<a href="'.$route.'#events" data-hover="tooltip" title="'.__('Events').'">
                <i class="far fa-calendar-alt"></i></a>';
    }

    /**
     * Return project events large icon
     *
     * @return string
     */
    public function projectEventsIconLrg()
    {
        $route = route("front.projects.slug", [$this->model->slug]);

        return $this->model->events_count == null ? '' :
            '<a href="'.$route.'#events" data-hover="tooltip" title="'.__('Events').'">
                <i class="far fa-calendar-alt fa-2x"></i></a>';
    }

    /**
     * Return organization icon
     *
     * @return string
     */
    public function organizationIcon()
    {
        return $this->model->organization_website == null ? '' :
            '<a href="'.$this->model->organization_website.'" target="_blank" data-hover="tooltip" title="'.__('Organization').'">
                <i class="fas fa-building"></i></a>';
    }

    /**
     * Return organization lrg icon
     *
     * @return string
     */
    public function organizationIconLrg()
    {
        return $this->model->organization_website == null ? '' :
            '<a href="'.$this->model->organization_website.'" target="_blank" data-hover="tooltip" title="'.__('Organization').'">
                <i class="fas fa-building fa-2x"></i></a>';
    }

    /**
     * Return twitter small icon
     *
     * @return string
     */
    public function twitterIcon()
    {
        return $this->model->twitter == null ? '' :
            '<a href="'.$this->model->twitter.'" target="_blank" data-hover="tooltip" title="'.__('Twitter').'">
                <i class="fab fa-twitter"></i></a>';
    }

    /**
     * Return twitter large icon
     *
     * @return string
     */
    public function twitterIconLrg()
    {
        return $this->model->twitter == null ? '' :
            '<a href="'.$this->model->twitter.'" target="_blank" data-hover="tooltip" title="'.__('Twitter').'">
                <i class="fab fa-twitter fa-2x"></i></a>';
    }

    /**
     * Return facebook small icon
     *
     * @return string
     */
    public function facebookIcon()
    {
        return $this->model->facebook == null ? '' :
            '<a href="'.$this->model->facebook.'" target="_blank" data-hover="tooltip" title="'.__('Facebook').'">
                <i class="fab fa-facebook"></i></a>';
    }

    /**
     * Return facebook large icon
     *
     * @return string
     */
    public function facebookIconLrg()
    {
        return $this->model->facebook == null ? '' :
            '<a href="'.$this->model->facebook.'" target="_blank" data-hover="tooltip" title="'.__('Facebook').'">
                <i class="fab fa-facebook fa-2x"></i></a>';
    }

    /**
     * Return blog small icon
     *
     * @return string
     */
    public function blogIcon()
    {
        return $this->model->blog_url == null ? '' :
            '<a href="'.$this->model->blog_url.'" target="_blank" data-hover="tooltip" title="'.__('Blog').'">
                <i class="fab fa-blogger-b"></i></a>';
    }

    /**
     * Return blog large icon
     *
     * @return string
     */
    public function blogIconLrg()
    {
        return $this->model->blog_url == null ? '' :
            '<a href="'.$this->model->blog_url.'" target="_blank" data-hover="tooltip" title="'.__('Blog').'">
                <i class="fab fa-blogger-b fa-2x"></i></a>';
    }

    /**
     * Return contact small icon
     *
     * @return string
     */
    public function contactEmailIcon()
    {
        return $this->model->contact_email == null ? '' :
            '<a href="mailto:'.$this->model->contact_email.'" data-hover="tooltip" title="'.__('Contact').'">
                <i class="fas fa-envelope"></i></a>';
    }

    /**
     * Return contact large icon
     *
     * @return string
     */
    public function contactEmailIconLrg()
    {
        return $this->model->contact_email == null ? '' :
            '<a href="mailto:'.$this->model->contact_email.'" data-hover="tooltip" title="'.__('Contact').'">
                <i class="fas fa-envelope fa-2x"></i></a>';
    }

    /**
     * Return expedition icon on project home page.
     *
     * @return string
     */
    public function projectExpeditionsIcon()
    {
        return '<a href="#expeditions" data-hover="tooltip" title="'.__('Expeditions').'"><i class="fas fa-binoculars"></i></a>';
    }

    /**
     * Return expedition icon on project home page.
     *
     * @return string
     */
    public function projectExpeditionsIconLrg()
    {
        return '<a href="#expeditions" data-hover="tooltip" title="'.__('Expeditions').'"><i class="fas fa-binoculars fa-2x"></i></a>';
    }

    /**
     * Return return explore project subjects icon.
     *
     * @return string
     */
    public function projectExploreIcon()
    {
        return '<a href="'.route('admin.projects.explore', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Explore Project Subjects').'"><i class="fas fa-table"></i></a>';
    }

    /**
     * Return return explore project subjects icon.
     *
     * @return string
     */
    public function projectExploreIconLrg()
    {
        return '<a href="'.route('admin.projects.explore', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Explore Project Subjects').'"><i class="fas fa-table fa-2x"></i></a>';
    }

    /**
     * Return view project icon.
     *
     * @return string
     */
    public function projectShowIcon()
    {
        return '<a href="'.route('admin.projects.show', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('View Project').'"><i class="fas fa-eye"></i></a>';
    }

    /**
     * Return view project icon.
     *
     * @return string
     */
    public function projectShowIconLrg()
    {
        return '<a href="'.route('admin.projects.show', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('View Project').'"><i class="fas fa-eye fa-2x"></i></a>';
    }

    /**
     * Return return advertise project icon.
     *
     * @return string
     */
    public function projectAdvertiseIcon()
    {
        return '<a href="'.route('admin.advertises.index', [$this->model->id]).'" 
                    data-hover="tooltip" title="'.__('Download Advertisement Manifest').'"><i class="fas fa-ad"></i></a>';
    }

    /**
     * Return return advertise project icon.
     *
     * @return string
     */
    public function projectAdvertiseIconLrg()
    {
        return '<a href="'.route('admin.advertises.index', [$this->model->id]).'" 
                    data-hover="tooltip" title="'.__('Download Advertisement Manifest').'"><i class="fas fa-ad fa-2x"></i></a>';
    }

    /**
     * Return return statistics project icon.
     *
     * @return string
     */
    public function projectStatisticsIcon()
    {
        return '<a href="'.route('admin.projects.statistics', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Project Statistics').'"><i class="fas fa-chart-bar"></i></a>';
    }

    /**
     * Return return statistics project icon.
     *
     * @return string
     */
    public function projectStatisticsIconLrg()
    {
        return '<a href="'.route('admin.projects.statistics', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Project Statistics').'"><i class="fas fa-chart-bar fa-2x"></i></a>';
    }

    /**
     * Return return edit project icon.
     *
     * @return string
     */
    public function projectEditIcon()
    {
        return '<a href="'.route('admin.projects.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Edit Project').'"><i class="fas fa-edit"></i></a>';
    }

    /**
     * Return return edit project icon.
     *
     * @return string
     */
    public function projectEditIconLrg()
    {
        return '<a href="'.route('admin.projects.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Edit Project').'"><i class="fas fa-edit fa-2x"></i></a>';
    }

    /**
     * Return return clone project icon.
     *
     * @return string
     */
    public function projectCloneIcon()
    {
        return '<a href="'.route('admin.projects.clone', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Clone Project').'"><i class="fas fa-clone"></i></a>';
    }

    /**
     * Return return clone project icon.
     *
     * @return string
     */
    public function projectCloneIconLrg()
    {
        return '<a href="'.route('admin.projects.clone', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Clone Project').'"><i class="fas fa-clone fa-2x"></i></a>';
    }

    /**
     * Return return delete project icon.
     *
     * @return string
     */
    public function projectDeleteIcon()
    {
        return '<a href="'.route('admin.projects.delete', [$this->model->id]).'" class="prevent-default"
            title="'.__('Delete Project').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-btn-ok-label="Confirm" data-btn-ok-icon="fas fa-share fa-2x"
            data-btn-ok-class="btn btn-primary"
            data-btn-cancel-label="Cancel" data-btn-cancel-icon="fas fa-ban fa-2x"
            data-btn-cancel-class="btn btn-primary"
            data-title="'.__('Delete Project').'?" data-content="'.__('This will permanently delete the Project and all records.').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return delete project icon.
     *
     * @return string
     */
    public function projectDeleteIconLrg()
    {
        return '<a href="'.route('admin.projects.delete', [$this->model->id]).'" class="prevent-default"
            title="'.__('Delete Project').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-btn-ok-label="Continue" data-btn-ok-icon="fas fa-share fa-2x"
            data-btn-ok-class="btn-success"
            data-btn-cancel-label="Stop" data-btn-cancel-icon="fas fa-ban fa-2x"
            data-btn-cancel-class="btn-danger"
            data-title="'.__('Delete Project').'?" data-content="'.__('This will permanently delete the Project and all records.').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }

    /**
     * Return return clone project icon.
     *
     * @return string
     */
    public function projectImportIcon()
    {
        return '<a href="#" class="preventDefault" 
                    data-remote="'.route('admin.imports.index', [$this->model->id]).'" 
                    data-toggle="modal" data-target="#import-modal" 
                    data-hover="tooltip" title="'.__('Import Project Subjects').'"><i class="fas fa-file-import"></i></a>';
    }

    /**
     * Return return clone project icon.
     *
     * @return string
     */
    public function projectImportIconLrg()
    {
        return '<a href="#" class="preventDefault"
                    data-remote="'.route('admin.imports.index', [$this->model->id]).'" 
                    data-toggle="modal" data-target="#import-modal" 
                    data-hover="tooltip" title="'.__('Import Project Subjects').'"><i class="fas fa-file-import fa-2x"></i></a>';
    }

    /**
     * Return return ocr icon.
     *
     * @return string
     */
    public function projectOcrIcon()
    {
        return '<a href="'.route('admin.projects.ocr', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('Reprocess Subject OCR').'" 
            data-hover="tooltip"        
            data-method="post"
            data-confirm="confirmation"
            data-btn-ok-label="Continue" data-btn-ok-icon="fas fa-share fa-2x"
            data-btn-ok-class="btn-success"
            data-btn-cancel-label="Stop" data-btn-cancel-icon="fas fa-ban fa-2x"
            data-btn-cancel-class="btn-danger"
            data-title="'.__('Reprocess Subject OCR').'?" data-content="'.__('This action will reprocess all ocr for entire Project.').'">
            <i class="fas fa-redo-alt"></i></a>';
    }

    /**
     * Return return ocr lrg icon.
     *
     * @return string
     */
    public function projectOcrIconLrg()
    {
        return '<a href="'.route('admin.projects.ocr', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('Reprocess Subject OCR').'" 
            data-hover="tooltip"        
            data-method="post"
            data-confirm="confirmation"
            data-btn-ok-label="Continue" data-btn-ok-icon="fas fa-share fa-2x"
            data-btn-ok-class="btn-success"
            data-btn-cancel-label="Stop" data-btn-cancel-icon="fas fa-ban fa-2x"
            data-btn-cancel-class="btn-danger"
            data-title="'.__('Reprocess Subject OCR').'?" data-content="'.__('This action will reprocess all ocr for entire Project.').'">
            <i class="fas fa-redo-alt fa-2x"></i></a>';
    }
}