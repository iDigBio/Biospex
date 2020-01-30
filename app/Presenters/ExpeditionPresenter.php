<?php

namespace App\Presenters;

class ExpeditionPresenter extends Presenter
{
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
            ]).'" data-hover="tooltip" title="'.__('pages.view').' '.__('pages.expedition').'">
            <i class="fas fa-eye"></i></a>';
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
            ]).'" data-hover="tooltip" title="'.__('pages.view').' '.__('pages.expedition').'">
            <i class="fas fa-eye fa-2x"></i></a>';
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
                    data-target="#expedition-download-modal" data-hover="tooltip" title="'.__('pages.expedition_download_files').'">
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
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' '.__('pages.expedition').'"><i class="fas fa-edit"></i></a>';
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
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' '.__('pages.expedition').'"><i class="fas fa-edit fa-2x"></i></a>';
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
            ]).'" data-hover="tooltip" title="'.__('pages.clone').' '.__('pages.expedition').'"><i class="fas fa-clone"></i></a>';
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
            ]).'" data-hover="tooltip" title="'.__('pages.clone').' '.__('pages.expedition').'"><i class="fas fa-clone fa-2x"></i></a>';
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
            title="'.__('pages.delete').' '.__('pages.expedition').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' '.__('pages.expedition').'?" data-content="'.__('messages.record_delete').'">
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
            title="'.__('pages.delete').' '.__('pages.expedition').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' '.__('pages.expedition').'?" 
            data-content="'.__('messages.record_delete').'">
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
            title="'.__('pages.ocr_reprocess').'" 
            data-hover="tooltip"        
            data-method="post"
            data-confirm="confirmation"
            data-title="'.__('pages.ocr_reprocess').'?" 
            data-content="'.__('messages.ocr_reprocess_message', ['record' => __('pages.expedition')]).'">
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
            title="'.__('pages.expedition_process').'" 
            data-hover="tooltip"        
            data-method="post"
            data-confirm="confirmation"
            data-title="'.__('pages.expedition_process').'?" 
            data-content="'.__('messages.expedition_process_message').'">
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
            title="'.__('pages.expedition_stop').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.expedition_stop').'" 
            data-content="'.__('messages.expedition_process_stop_message').'">
            <i class="fas fa-stop-circle fa-2x"></i></a>';
    }

    /**
     * Return large icon and path for reprocessing export file.
     *
     * @return string
     */
    public function expeditionRegenerateExportLrg()
    {
        return '<a class="float-right mr-4" href="'.route('admin.downloads.regenerate', [
                $this->model->project->id,
                $this->model->id,
            ]).'"
                   data-hover="tooltip"
                   title="'.__('pages.generate_export').'">
                    <i class="fas fa-file-export fa-2x"></i></a>';
    }
}