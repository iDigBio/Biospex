<?php
/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Presenters;

use Storage;

/**
 * Class ExpeditionPresenter
 *
 * @package App\Presenters
 */
class ExpeditionPresenter extends Presenter
{
    /**
     * Check if logo file exists or return default.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public function showMediumLogo()
    {
        $baseLength = config('paperclip.storage.base-urls.public');
        $url = $this->model->logo->url('medium');
        $path = str_replace($baseLength, '', $url);

        return Storage::disk('public')->exists($path) ? $url : config('config.missing_expedition_logo');
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
            ]).'" data-hover="tooltip" title="'. t('View Expedition').'">
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
            ]).'" data-hover="tooltip" title="'. t('View Expedition').'">
            <i class="fas fa-eye fa-2x"></i></a>';
    }

    /**
     * Return tools icon.
     *
     * @return string
     */
    public function expeditionToolsIconLrg()
    {
        return '<a href="#" class="preventDefault" data-toggle="modal"
                    data-target="#expedition-tools-modal" data-hover="tooltip" title="'. t('Expedition Tools').'">
                    <i class="fas fa-tools fa-2x"></i></a>';
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
                    data-target="#expedition-download-modal" data-hover="tooltip" title="'. t('Download Expedition Files').'">
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
            ]).'" data-hover="tooltip" title="'. t('Edit Expedition').'"><i class="fas fa-edit"></i></a>';
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
            ]).'" data-hover="tooltip" title="'. t('Edit Expedition').'"><i class="fas fa-edit fa-2x"></i></a>';
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
            ]).'" data-hover="tooltip" title="'. t('Clone Expedition').'"><i class="fas fa-clone"></i></a>';
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
            ]).'" data-hover="tooltip" title="'. t('Clone Expedition').'"><i class="fas fa-clone fa-2x"></i></a>';
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
            title="'. t('Delete Expedition').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'. t('Delete Expedition').'?" data-content="'. t('This will permanently delete the record and all associated records.').'">
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
            title="'. t('Delete Expedition').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'. t('Delete Expedition').'?" 
            data-content="'. t('This will permanently delete the record and all associated records.').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }

    /**
     * Return return ocr lrg icon.
     *
     * @return string
     */
    public function expeditionOcrBtn()
    {
        return '<a href="'.route('admin.expeditions.ocr', [
                $this->model->project_id,
                $this->model->id,
            ]).'" class="prevent-default btn btn-primary rounded-0 mb-1 mt-1"
            data-method="post"
            data-confirm="confirmation"
            data-title="'. t('Reprocess Subject OCR').'?" 
            data-content="'. t('This action will reprocess all ocr for the Expedition.').'">
            '. t('Reprocess Subject OCR').'</a>';
    }

    /**
     * Return lrg icon for expedition process.
     *
     * @return string
     */
    public function expeditionProcessStartBtn()
    {
        return '<a href="'.route('admin.expeditions.process', [
                $this->model->project_id,
                $this->model->id,
            ]).'" class="prevent-default btn btn-primary rounded-0 mb-1 green" 
            data-method="post"
            data-confirm="confirmation"
            data-title="'. t('Start Expedition Processing').'?" 
            data-content="'. t('This will begin processing the Expedition. After starting, Subjects cannot be added or removed. Do you wish to Continue?').'">
            '. t('Start Expedition Processing').'</a>';
    }

    /**
     * Return lrg icon for expedition process stop
     *
     * @return string
     */
    public function expeditionProcessStopBtn()
    {
        return '<a href="'.route('admin.expeditions.stop', [
                $this->model->project_id,
                $this->model->id,
            ]).'" class="prevent-default btn btn-primary rounded-0 mb-1"
            data-method="delete"
            data-confirm="confirmation"
            data-title="'. t('Stop Processing').'" 
            data-content="'. t('This will stop the Expedition Process. However, Subjects cannot be added since process was already started. Do you wish to Continue?').'">
            '. t('Stop Expedition Processing').'</a>';
    }

    /**
     * Return button and path for generating export file.
     *
     * @return string
     */
    public function expeditionExportBtn()
    {
        $route = route('admin.downloads.export', [
            $this->model->project->id,
            $this->model->id,
        ]);

        return '<a href="'.$route.'" class="prevent-default btn btn-primary rounded-0 mb-1"
            data-method="get"
            data-confirm="confirmation"
            data-title="'. t('Generate Export File').'" 
            data-content="'. t('This will generate a new export file. Any previous exports will be overwritten. Do you wish to Continue?').'">
            '. t('Generate Export File').'</a>';
    }

    /**
     * Return button and path for generating export file.
     *
     * @return string
     */
    public function expeditionWorkflowBtn()
    {
        return '<a href="" class="prevent-default btn btn-primary rounded-0 mb-1"
            data-dismiss="modal"
            data-toggle="modal"
            data-target="#expedition-workflow-form-modal"
            data-title="'. t('Edit Workflow Id').'">'.t('Edit Workflow Id').'</a>';
    }
}