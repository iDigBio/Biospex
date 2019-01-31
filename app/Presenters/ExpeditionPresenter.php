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
     * Return view icon.
     *
     * @return string
     */
    public function expeditionShowIcon()
    {
        return '<a href="'.route('admin.expeditions.show', [
                $this->model->project_id,
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('View Expedition').'"><i class="fas fa-eye"></i></a>';
    }

    /**
     * Return return download icon.
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
                    data-target="#expedition-download-modal" data-hover="tooltip" title="'.__('Download Expedition Files').'">
                    <i class="fas fa-file-download"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function expeditionEditIcon()
    {
        return '<a href="'.route('admin.expeditions.edit', [
                $this->model->project_id,
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Edit Expedition').'"><i class="fas fa-edit"></i></a>';
    }

    /**
     * Return return clone icon.
     *
     * @return string
     */
    public function expeditionCloneIcon()
    {
        return '<a href="'.route('admin.expeditions.clone', [
                $this->model->project_id,
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Clone Expedition').'"><i class="fas fa-clone"></i></a>';
    }

    /**
     * Return return delete icon.
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
            data-title="'.__('Delete Expedition').'?" data-content="'.__('This will permanently delete the record').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return ocr icon.
     *
     * @return string
     */
    public function expeditionOcrIcon()
    {
        return '<a href="'.route('admin.expeditions.ocr', [
                $this->model->project_id,
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
            data-title="'.__('Reprocess Subject OCR').'?" data-content="'.__('This action will reprocess all ocr for the Expedition.').'">
            <i class="fas fa-redo-alt"></i></a>';
    }

    /**
     * Return return ocr lrg icon.
     *
     * @return string
     */
    public function expeditionOcrIconLrg()
    {
        return '<a href="'.route('admin.expeditions.ocr', [
                $this->model->project_id,
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
            data-title="'.__('Reprocess Subject OCR').'?" data-content="'.__('This action will reprocess all ocr for the Expedition.').'">
            <i class="fas fa-redo-alt fa-2x"></i></a>';
    }
}