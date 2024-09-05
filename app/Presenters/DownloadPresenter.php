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

use Illuminate\Support\Facades\Storage;

/**
 * Class DownloadPresenter
 *
 * @package App\Presenters
 */
class DownloadPresenter extends Presenter
{
    /**
     * Returns event date as string.
     *
     * @return string
     */
    public function fileType()
    {
        if ($this->model->type === 'reconciled-with-expert' || $this->model->type === 'reconciled-with-user') {
            return str_replace('-', '_', $this->model->type) . '_opinion';
        }

        if ($this->model->type === 'report') {
            return 'export_report';
        }

        return $this->model->type;
    }

    /**
     * Return export file url.
     *
     * @return string
     */
    public function exportDownload(): string
    {
        $filename = "{$this->model->type}-{$this->model->file}";
        return $this->model->actor_id == config('zooniverse.actor_id') ?
            Storage::disk('s3')->temporaryUrl(config('config.export_dir').'/'.$this->model->file, now()->addMinutes(30), ['ResponseContentDisposition' => 'attachment;filename=zooniverse-'.$filename]) :
            Storage::disk('s3')->temporaryUrl(config('geolocate.dir.export').'/'.$this->model->file, now()->addMinutes(30), ['ResponseContentDisposition' => 'attachment;filename=geolocate-'.$filename]);
    }

    /**
     * Return report file url.
     *
     * @return string
     */
    public function reportDownload(): string
    {
        return Storage::disk('s3')->temporaryUrl(config('config.report_dir').'/'.$this->model->file, now()->addMinutes(30), ['ResponseContentDisposition' => 'attachment']);
    }

    /**
     * Return csv file url.
     *
     * @return string
     */
    public function downloadType(): string
    {
        $filename = "{$this->model->type}-{$this->model->file}";
        return Storage::disk('s3')->temporaryUrl(config('zooniverse.directory.'.$this->model->type).'/'.$this->model->file, now()->addMinutes(30), ['ResponseContentDisposition' => 'attachment;filename='.$filename]);
    }
}