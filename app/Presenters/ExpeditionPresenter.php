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
    public function expeditionOcrIconLrg()
    {
        return '<a href="'.route('admin.expeditions.ocr', [
                $this->model->project_id,
                $this->model->id,
            ]).'" class="prevent-default"
            title="'. t('Reprocess Subject OCR').'" 
            data-hover="tooltip"        
            data-method="post"
            data-confirm="confirmation"
            data-title="'. t('Reprocess Subject OCR').'?" 
            data-content="'. t('This action will reprocess all ocr for the Expedition.').'">
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
            title="'. t('Start Expedition Processing').'" 
            data-hover="tooltip"        
            data-method="post"
            data-confirm="confirmation"
            data-title="'. t('Start Expedition Processing').'?" 
            data-content="'. t('This will begin processing the Expedition. After starting, Subjects cannot be added or removed. Do you wish to Continue?').'">
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
            title="'. t('Stop Expedition Processing').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'. t('Stop Expedition Processing').'" 
            data-content="'. t('This will stop the Expedition Process. However, Subjects cannot be added since process was already started. Do you wish to Continue?').'">
            <i class="fas fa-stop-circle fa-2x"></i></a>';
    }

    /**
     * Return button and path for reprocessing export file.
     *
     * @return string
     */
    public function expeditionRegenerateExportBtn()
    {
        $route = route('admin.downloads.regenerate', [
            $this->model->project->id,
            $this->model->id,
        ]);

        return '<a class="btn btn-primary float-right mt-2 mb-2" href="'.$route.'">'. t('Regenerate Export File').'</a>';
    }
}