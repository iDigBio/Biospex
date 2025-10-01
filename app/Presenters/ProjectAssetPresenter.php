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
 * Class ProjectAssetPresenter
 */
class ProjectAssetPresenter extends Presenter
{
    /**
     * Build site-asset link with support for new Livewire path and legacy paperclip.
     *
     * @return string
     */
    public function resource()
    {
        $name = $this->model->name;
        $description = $this->model->description;

        if ($this->model->type === 'File Download') {
            $url = null;

            // Check for new Livewire download_path first (check S3 for new uploads)
            if (! empty($this->model->download_path)) {
                if (Storage::disk('s3')->exists($this->model->download_path)) {
                    // Generate a temporary signed URL for private S3 files (valid for 1 hour)
                    $url = Storage::disk('s3')->temporaryUrl($this->model->download_path, now()->addHour());
                }
            }

            // Fallback to legacy paperclip logic during transition
            if (! $url && ! empty($this->model->download_file_name)) {
                $baseLength = config('paperclip.storage.base-urls.public');
                $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $this->model->id);
                $paperclipPath = "/paperclip/App/Models/ProjectAsset/downloads/{$idPartition}/original/{$this->model->download_file_name}";
                $url = $baseLength.$paperclipPath;

                if (! Storage::disk('public')->exists($paperclipPath)) {
                    $url = null;
                }
            }

            if ($url) {
                return '<a href="'.$url.'" target="_blank" data-hover="tooltip" title="'.$description.'">
                <i class="fas fa-file"></i> '.$name.'</a>';
            }
        }

        return '<a href="'.$name.'" target="_blank" data-hover="tooltip" title="'.$description.'">
            <i class="fas fa-link"></i> '.$name.'</a>';
    }
}
