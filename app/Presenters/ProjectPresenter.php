<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Presenters;

use Storage;

/**
 * Class ProjectPresenter
 */
class ProjectPresenter extends Presenter
{
    /**
     * Check if logo file exists or return default.
     * Supports both new Livewire path and legacy paperclip during transition.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public function showLogo()
    {
        // Check for new Livewire logo_path first (check S3 for new uploads)
        if (! empty($this->model->logo_path)) {
            if (Storage::disk('s3')->exists($this->model->logo_path)) {
                // Generate a temporary signed URL for private S3 files (valid for 1 hour)
                return Storage::disk('s3')->temporaryUrl($this->model->logo_path, now()->addHour());
            }
        }

        // Fallback to legacy paperclip logic during transition
        if (! empty($this->model->logo_file_name)) {
            \Log::info('Checking for paperclip logo file: '.$this->model->logo_file_name);
            $baseLength = config('paperclip.storage.base-urls.public');
            $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $this->model->id);
            $paperclipPath = "/paperclip/App/Models/Project/logos/{$idPartition}/original/{$this->model->logo_file_name}";
            $url = $baseLength.$paperclipPath;

            if (Storage::disk('public')->exists($paperclipPath)) {
                return $url;
            }
        }

        // Return default missing logo
        return config('config.missing_project_logo');
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

        return $banner === null ? '/images/habitat-banners/banner-trees.jpg' : '/images/habitat-banners/'.$banner;
    }

    /**
     * Return project home button
     *
     * @return string
     */
    public function projectPageIcon()
    {
        $route = route('front.projects.show', [$this->model->slug]);

        return $this->model->slug == null ? '' : '<a href="'.$route.'" 
            data-hover="tooltip" 
            title="'.t('Project Public Page').'"><i class="fas fa-project-diagram"></i></a>';
    }

    /**
     * Return project home button
     *
     * @return string
     */
    public function projectPageIconLrg()
    {
        $route = route('front.projects.show', [$this->model->slug]);

        return $this->model->slug == null ? '' : '<a href="'.$route.'" 
            target="_blank" 
            data-hover="tooltip" 
            title="'.t('Project Public Page').'"><i class="fas fa-project-diagram fa-2x"></i></a>';
    }

    /**
     * Return project home button
     *
     * @return string
     */
    public function projectAdminIconLrg()
    {
        $route = route('admin.projects.show', [$this->model]);

        return $this->model->id == null ? '' : '<a href="'.$route.'" 
            data-hover="tooltip" 
            title="'.t('Show Project Admin Page').'"><i class="fas fa-project-diagram fa-2x"></i></a>';
    }

    /**
     * Return project events small icon
     *
     * @return string
     */
    public function projectEventsIcon()
    {
        $route = route('front.projects.show', [$this->model->slug]);

        return $this->model->events_count == null ? '' : '<a href="'.$route.'#events" data-hover="tooltip" title="'.t('Events').'">
                <i class="far fa-calendar-alt"></i></a>';
    }

    /**
     * Return project events large icon
     *
     * @return string
     */
    public function projectEventsIconLrg()
    {
        $route = route('front.projects.show', [$this->model->slug]);

        return $this->model->events_count == null ? '' : '<a href="'.$route.'#events" data-hover="tooltip" title="'.t('Events').'">
                <i class="far fa-calendar-alt fa-2x"></i></a>';
    }

    /**
     * Return organization icon
     *
     * @return string
     */
    public function organizationIcon()
    {
        return $this->model->organization_website == null ? '' : '<a href="'.$this->model->organization_website.'" target="_blank" data-hover="tooltip" title="'.t('Organization').'">
                <i class="fas fa-building"></i></a>';
    }

    /**
     * Return organization lrg icon
     *
     * @return string
     */
    public function organizationIconLrg()
    {
        return $this->model->organization_website == null ? '' : '<a href="'.$this->model->organization_website.'" target="_blank" data-hover="tooltip" title="'.t('Organization').'">
                <i class="fas fa-building fa-2x"></i></a>';
    }

    /**
     * Return twitter small icon
     *
     * @return string
     */
    public function twitterIcon()
    {
        return $this->model->twitter == null ? '' : '<a href="'.$this->model->twitter.'" target="_blank" data-hover="tooltip" title="'.t('Twitter').'">
                <i class="fab fa-twitter"></i></a>';
    }

    /**
     * Return twitter large icon
     *
     * @return string
     */
    public function twitterIconLrg()
    {
        return $this->model->twitter == null ? '' : '<a href="'.$this->model->twitter.'" target="_blank" data-hover="tooltip" title="'.t('Twitter').'">
                <i class="fab fa-twitter fa-2x"></i></a>';
    }

    /**
     * Return facebook small icon
     *
     * @return string
     */
    public function facebookIcon()
    {
        return $this->model->facebook == null ? '' : '<a href="'.$this->model->facebook.'" target="_blank" data-hover="tooltip" title="'.t('Facebook').'">
                <i class="fab fa-facebook"></i></a>';
    }

    /**
     * Return facebook large icon
     *
     * @return string
     */
    public function facebookIconLrg()
    {
        return $this->model->facebook == null ? '' : '<a href="'.$this->model->facebook.'" target="_blank" data-hover="tooltip" title="'.t('Facebook').'">
                <i class="fab fa-facebook fa-2x"></i></a>';
    }

    /**
     * Return blog small icon
     *
     * @return string
     */
    public function blogIcon()
    {
        return $this->model->blog_url == null ? '' : '<a href="'.$this->model->blog_url.'" target="_blank" data-hover="tooltip" title="'.t('Blog').'">
                <i class="fab fa-blogger-b"></i></a>';
    }

    /**
     * Return blog large icon
     *
     * @return string
     */
    public function blogIconLrg()
    {
        return $this->model->blog_url == null ? '' : '<a href="'.$this->model->blog_url.'" target="_blank" data-hover="tooltip" title="'.t('Blog').'">
                <i class="fab fa-blogger-b fa-2x"></i></a>';
    }

    /**
     * Return contact small icon
     *
     * @return string
     */
    public function contactEmailIcon()
    {
        return $this->model->contact_email == null ? '' : '<a href="mailto:'.$this->model->contact_email.'" data-hover="tooltip" title="'.t('Contact').'">
                <i class="fas fa-envelope"></i></a>';
    }

    /**
     * Return contact large icon
     *
     * @return string
     */
    public function contactEmailIconLrg()
    {
        return $this->model->contact_email == null ? '' : '<a href="mailto:'.$this->model->contact_email.'" data-hover="tooltip" title="'.t('Contact').'">
                <i class="fas fa-envelope fa-2x"></i></a>';
    }

    /**
     * Return expedition icon on project home page.
     *
     * @return string
     */
    public function projectExpeditionsIcon()
    {
        return '<a href="#expeditions" data-hover="tooltip" title="'.t('Expeditions').'"><i class="fas fa-binoculars"></i></a>';
    }

    /**
     * Return expedition icon on project home page.
     *
     * @return string
     */
    public function projectExpeditionsIconLrg()
    {
        return '<a href="#expeditions" data-hover="tooltip" title="'.t('Expeditions').'"><i class="fas fa-binoculars fa-2x"></i></a>';
    }

    /**
     * Return return explore project subjects icon.
     *
     * @return string
     */
    public function projectExploreIconLrg()
    {
        return '<a href="'.route('admin.project-subjects.index', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('Explore Project Subjects').'"><i class="fas fa-table fa-2x"></i></a>';
    }

    /**
     * Return view project icon.
     *
     * @return string
     */
    public function projectShowIcon()
    {
        return '<a href="'.route('admin.projects.show', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('View Project').'"><i class="fas fa-eye"></i></a>';
    }

    /**
     * Return view project icon.
     *
     * @return string
     */
    public function projectShowIconLrg()
    {
        return '<a href="'.route('admin.projects.show', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('View Project').'"><i class="fas fa-eye fa-2x"></i></a>';
    }

    /**
     * Return return advertise project icon.
     *
     * @return string
     */
    public function projectAdvertiseIconLrg()
    {
        return '';
        /* Disabled until Austin wants to bring it back.
        return '<a href="'.route('admin.advertises.index', [$this->model]).'"
                    data-hover="tooltip"
                    title="'.t('Download Advertisement Manifest').'"><i class="fas fa-ad fa-2x"></i></a>';
        */
    }

    /**
     * Return return statistics project icon.
     *
     * @return string
     */
    public function projectStatisticsIconLrg()
    {
        return '<a href="'.route('admin.project-stats.index', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('Project Statistics').'"><i class="fas fa-chart-bar fa-2x"></i></a>';
    }

    /**
     * Return return edit project icon.
     *
     * @return string
     */
    public function projectEditIcon()
    {
        return '<a href="'.route('admin.projects.edit', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('Edit Project').'"><i class="fas fa-edit"></i></a>';
    }

    /**
     * Return return edit project icon.
     *
     * @return string
     */
    public function projectEditIconLrg()
    {
        return '<a href="'.route('admin.projects.edit', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('Edit Project').'"><i class="fas fa-edit fa-2x"></i></a>';
    }

    /**
     * Return return clone project icon.
     *
     * @return string
     */
    public function projectCloneIcon()
    {
        return '<a href="'.route('admin.projects.clone', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('Clone Project').'"><i class="fas fa-clone"></i></a>';
    }

    /**
     * Return return clone project icon.
     *
     * @return string
     */
    public function projectCloneIconLrg()
    {
        return '<a href="'.route('admin.projects.clone', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('Clone Project').'"><i class="fas fa-clone fa-2x"></i></a>';
    }

    /**
     * Return return delete project icon.
     *
     * @return string
     */
    public function projectDeleteIcon()
    {
        return '<a href="'.route('admin.projects.destroy', [$this->model]).'" class="prevent-default"
            title="'.t('Delete Project').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.t('Delete Project').'?" data-content="'.t('This will permanently delete the record and all associated records.').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return delete project icon.
     *
     * @return string
     */
    public function projectDeleteIconLrg()
    {
        return '<a href="'.route('admin.projects.destroy', [$this->model]).'" class="prevent-default"
            title="'.t('Delete Project').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.t('Delete Project').'?" data-content="'.t('This will permanently delete the record and all associated records.').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }

    /**
     * Return return clone project icon.
     *
     * @return string
     */
    public function projectImportIconLrg()
    {
        return '<a href="#" class="prevent-default" 
                    data-url="'.route('admin.imports.index', [$this->model]).'" 
                    data-dismiss="modal" data-toggle="modal" data-target="#global-modal" data-size="modal-lg"
                    data-title="'.t('Import Project Subjects').'"
                    data-hover="tooltip" title="'.t('Import Project Subjects').'">
                    <i class="fas fa-file-import fa-2x"></i></a>';
    }

    /**
     * Return return ocr lrg icon.
     *
     * @return string
     */
    public function projectOcrIconLrg()
    {
        return '<a href="'.route('admin.projects.ocr', [
            $this->model,
        ]).'" class="prevent-default"
            title="'.t('Reprocess Subject OCR').'" 
            data-hover="tooltip"        
            data-method="post"
            data-confirm="confirmation"
            data-title="'.t('Reprocess Subject OCR').'?" data-content="'.t('This action will reprocess all ocr for the Project.').'">
            <i class="fas fa-redo-alt fa-2x"></i></a>';
    }

    /**
     * Return project link.
     *
     * @return string
     */
    public function titleLink()
    {
        return '<a href="'.route('admin.projects.show', [$this->model]).'">'.$this->model->title.'</a>';
    }
}
