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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Encode a full url.
 */
function url_encode($url): string
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->urlEncode($url);
}

/**
 * Get the state.
 */
function get_state($input): mixed
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->getState($input);
}

/**
 * Force UTF-8 encoding.
 */
function force_utf8($str, $inputEnc = 'WINDOWS-1252'): string
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->forceUtf8($str, $inputEnc);
}

/**
 * Human-readable file size.
 */
function human_file_size($bytes, $decimals = 2): string
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->humanFilesize($bytes, $decimals);
}

/**
 * Get the project banner file name.
 */
function project_banner_file_name(?string $name = null): ?string
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->projectBannerFileName($name);
}

/**
 * Get the project banner file URL.
 */
function project_banner_file_url(?string $name = null)
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->projectBannerFileUrl($name);
}

/**
 * Return default logo for projects.
 */
function project_default_logo(): string
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->projectDefaultLogo();
}

/**
 * Return default logo for expeditions.
 */
function expedition_default_logo(): string
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->expeditionDefaultLogo();
}

/**
 * Check if download file exists.
 * TODO: Refactor this after changing and moving download file storage.
 */
function download_file_exists(string $file, string $type, ?int $actorId = null): bool
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->downloadFileExists($file, $type, $actorId);
}

/**
 * Get file size of download file.
 */
function download_file_size(string $file, string $type, ?int $actorId = null): ?int
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->downloadFileSize($file, $type, $actorId);
}

/**
 * Check subjects and export file existence.
 */
function zooniverse_export_file_check(\App\Models\Expedition $expedition): bool
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->zooniverseExportFileCheck($expedition);
}

/**
 * Check panoptes workflow and project set.
 */
function check_panoptes_workflow(\App\Models\Expedition $expedition): bool
{
    $generalService = app(App\Services\Helpers\GeneralService::class);

    return $generalService->checkPanoptesWorkflow($expedition);
}
