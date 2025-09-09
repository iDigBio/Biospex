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
 * Class ProfilePresenter
 */
class ProfilePresenter extends Presenter
{
    /**
     * Check if avatar file exists or return default.
     * Supports both new Livewire path and legacy paperclip during transition.
     *
     * @return string
     */
    public function showAvatar()
    {
        // Check for new Livewire avatar_path first (check S3 for new uploads)
        if (! empty($this->model->avatar_path)) {
            \Log::info('Checking for new avatar: '.$this->model->avatar_path);
            if (Storage::disk('s3')->exists($this->model->avatar_path)) {
                \Log::info('Found avatar on S3: '.$this->model->avatar_path);

                // Generate a temporary signed URL for private S3 files (valid for 1 hour)
                return Storage::disk('s3')->temporaryUrl($this->model->avatar_path, now()->addHour());
            }
        }

        // Fallback to legacy paperclip logic during transition
        if (! empty($this->model->avatar_file_name)) {
            \Log::info('Checking for legacy avatar: '.$this->model->avatar_file_name);
            $baseLength = config('paperclip.storage.base-urls.public');
            $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $this->model->id);
            $paperclipPath = "/paperclip/App/Models/Profile/avatars/{$idPartition}/original/{$this->model->avatar_file_name}";
            $url = $baseLength.$paperclipPath;

            if (Storage::disk('public')->exists($paperclipPath)) {
                return $url;
            }
        }

        \Log::info('No avatar found for profile: '.$this->model->id);

        // Return default missing avatar
        return config('config.missing_user_avatar', '/images/default-avatar.png');
    }
}
