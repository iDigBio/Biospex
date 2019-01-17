<?php

namespace App\Presenters;

use Storage;

class ExpeditionPresenter extends Presenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function logoUrl()
    {
        $logo = $this->model->logo;

        return $this->variantExists($logo) ? $logo->url() : Storage::url('images/placeholders/card-image-place-holder02.jpg');
    }

    /**
     * Return view expedition icon.
     *
     * @return string
     */
    public function expeditionShowIcon()
    {
        return '<a href="'.route('admin.expeditions.show', [
                $this->model->project_id,
                $this->model->id,
            ]).'" data-hover="tooltip" title="View Expedition"><i class="fas fa-eye"></i></a>';
    }

    /**
     * Return return clone expedition icon.
     *
     * @return string
     */
    public function expeditionDownloadIcon()
    {
        $route = route('admin.downloads.index', [
            $this->model->project_id,
            $this->model->id,
        ]);

        return '<a href="#" class="preventDefault" data-toggle="modal" data-remote="'.$route.'" 
                    data-target="#expedition-download-modal" data-hover="tooltip" title="Download Expedition Files">
                    <i class="fas fa-file-download"></i></a>';
    }

    /**
     * Return return edit expedition icon.
     *
     * @return string
     */
    public function expeditionEditIcon()
    {
        return '<a href="'.route('admin.expeditions.edit', [
                $this->model->project_id,
                $this->model->id,
            ]).'" data-hover="tooltip" title="Edit Expedition"><i class="fas fa-edit"></i></a>';
    }

    /**
     * Return return clone expedition icon.
     *
     * @return string
     */
    public function expeditionCloneIcon()
    {
        return '<a href="'.route('admin.expeditions.clone', [
                $this->model->project_id,
                $this->model->id,
            ]).'" data-hover="tooltip" title="Clone Expedition"><i class="fas fa-clone"></i></a>';
    }

    /**
     * Return return delete project icon.
     *
     * @return string
     */
    public function expeditionDeleteIcon()
    {
        return '<a href="'.route('admin.expeditions.delete', [
                $this->model->project_id,
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('Delete Expedition').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-btn-ok-label="Continue" data-btn-ok-icon="fas fa-share fa-2x"
            data-btn-ok-class="btn-success"
            data-btn-cancel-label="Stop" data-btn-cancel-icon="fas fa-ban fa-2x"
            data-btn-cancel-class="btn-danger"
            data-title="Continue action?" data-content="This will permanently delete the record">
            <i class="fas fa-trash-alt"></i></a>';
    }

    public function downloadIcon()
    {
        dd($this->model);
        $route = route('admin.downloads.download', [
            $this->model->project->id,
            $this->model->id,
            $this->model->download->id]
        );

        return '<a href="'.$route.'" class="ajax-download" data-hover="tooltip" title="'. __('Download') . ' ' . $this->model->type . '">';
    }
}