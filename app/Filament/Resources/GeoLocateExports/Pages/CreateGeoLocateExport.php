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

namespace App\Filament\Resources\GeoLocateExports\Pages;

use App\Filament\Resources\GeoLocateExports\GeoLocateExportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGeoLocateExport extends CreateRecord
{
    protected static string $resource = GeoLocateExportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Data coming from form is already in correct format for MongoDB
        return $data;
    }
}
