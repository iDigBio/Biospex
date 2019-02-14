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

        return $this->variantExists($logo) ?
            $logo->url() : Storage::url('images/placeholders/card-image-place-holder02.jpg');
    }

    /**
     * Return show icon.
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
     * Return show icon lrg.
     *
     * @return string
     */
    public function expeditionShowIconLrg()
    {
        return '<a href="'.route('admin.expeditions.show', [
                $this->model->project_id,
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('View Expedition').'"><i class="fas fa-eye fa-2x"></i></a>';
    }

    /**
     * Return return download icon lrg.
     *
     * @return string
     */
    public function expeditionDownloadIconLrg()
    {
        $route = route('admin.downloads.index', [
            $this->model->project_id,
            $this->model->id,
        ]);

        return '<a href="#" class="preventDefault" data-toggle="modal" data-remote="'.$route.'" 
                    data-target="#expedition-download-modal" data-hover="tooltip" title="'.__('Download Expedition Files').'">
                    <i class="fas fa-file-download fa-2x"></i></a>';
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
     * Return return edit icon lrg.
     *
     * @return string
     */
    public function expeditionEditIconLrg()
    {
        return '<a href="'.route('admin.expeditions.edit', [
                $this->model->project_id,
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Edit Expedition').'"><i class="fas fa-edit fa-2x"></i></a>';
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
     * Return return clone icon lrg.
     *
     * @return string
     */
    public function expeditionCloneIconLrg()
    {
        return '<a href="'.route('admin.expeditions.clone', [
                $this->model->project_id,
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('Clone Expedition').'"><i class="fas fa-clone fa-2x"></i></a>';
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
            data-title="'.__('Delete Expedition').'?" data-content="'.__('This will permanently delete the record').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function expeditionDeleteIconLrg()
    {
        return '<a href="'.route('admin.expeditions.delete', [
                $this->model->project_id,
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('Delete Expedition').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('Delete Expedition').'?" 
            data-content="'.__('This will permanently delete the record').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
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
            data-title="'.__('Reprocess Subject OCR').'?" 
            data-content="'.__('This action will reprocess all ocr for the Expedition.').'">
            <i class="fas fa-redo-alt fa-2x"></i></a>';
    }

    /**
     * Return lrg icon for expedition process.
     *
     * @return string
     */
    public function expeditionProcessStartLrg()
    {
        return '<a href="'.route('admin.expeditions.process', [
                $this->model->project_id,
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('Start Expedition Processing').'" 
            data-hover="tooltip"        
            data-method="post"
            data-confirm="confirmation"
            data-title="'.__('Expedition Process').'?" 
            data-content="'.__('This will begin processing the Expedition. After starting, Subjects cannot be added or removed. Do you wish to Continue?').'">
            <i class="fas fa-play-circle fa-2x"></i></a>';
    }

    /**
     * Return lrg icon for expedition process stop
     *
     * @return string
     */
    public function expeditionProcessStopLrg()
    {
        return '<a href="'.route('admin.expeditions.stop', [
                $this->model->project_id,
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('Stop Expedition Processing').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('Expedition Process').'" 
            data-content="'.__('This will stop the Expedition Process. However, Subjects cannot be added since process was already started. Do you wish to Continue?').'">
            <i class="fas fa-stop-circle fa-2x"></i></a>';
    }
}