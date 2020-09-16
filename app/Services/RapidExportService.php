<?php
/**
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

namespace App\Services;

use App\Models\ExportForm;
use Storage;

class RapidExportService extends RapidServiceBase
{
    /**
     * @var \App\Services\RapidExportDbService
     */
    private $rapidExportDbService;

    /**
     * @var \App\Services\GeoLocateExportService
     */
    private $geoLocateExportService;

    /**
     * RapidExportService constructor.
     *
     * @param \App\Services\RapidExportDbService $rapidExportDbService
     * @param \App\Services\GeoLocateExportService $geoLocateExportService
     */
    public function __construct(
        RapidExportDbService $rapidExportDbService,
        GeoLocateExportService $geoLocateExportService
    )
    {
        $this->rapidExportDbService = $rapidExportDbService;
        $this->geoLocateExportService = $geoLocateExportService;
    }

    /**
     * Get header.
     *
     * @return mixed
     */
    public function getHeader()
    {
        $protected = config('config.protected_fields');

        $rapidHeader = $this->rapidExportDbService->getFirstRapidHeader();

        return collect($rapidHeader->header)->reject(function ($field) use ($protected) {
            return in_array($field, $protected);
        });
    }

    /**
     * Map the posted export order data.
     *
     * @param array $data
     * @return array
     */
    public function mapOrderFields(array $data): array
    {
        $data['exportFields'] = collect($data['exportFields'])->map(function($array){
            return collect($array)->map(function ($item, $key) {
                if ($key === 'order') {
                    return $item === null ? null : explode(',', $item);
                }

                return $item;
            });
        })->forget(['_token'])->toArray();

        unset($data['_token']);

        return $data;
    }

    /**
     * Safe the export form data.
     *
     * @param array $fields
     * @return \App\Models\ExportForm
     */
    public function saveForm(array $fields): ExportForm
    {
        return $this->rapidExportDbService->saveRapidForm($fields);
    }

    /**
     * Get forms by destination.
     *
     * @param string $destination
     * @return \Illuminate\Support\Collection
     */
    public function getFormsByDestination(string $destination)
    {
        return $this->rapidExportDbService->getRapidFormsByDestination($destination);
    }

    /**
     * Show GeoLocate form. If exists, build correct data.
     *
     * @param int|null $id
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function showGeoLocateFrm(int $id = null)
    {
        $form = $id === null ? null : $this->rapidExportDbService->findRapidFormById($id);

        $count = $form === null ? old('entries', 1) : $form->data['entries'];
        $geoLocateFields = json_decode(Storage::get(config('config.geolocate_fields_file')), true);
        $headers = $this->getHeader();
        $tags = $this->mapColumns($headers);

        $data = [
            'count' => $count,
            'exportType' => null,
            'geoLocateFields' => $geoLocateFields,
            'tags' => $tags,
            'frmData' => null
        ];

        if ($form === null) {
            return $data;
        }

        $frmData = null;
        for($i=0; $i < $count; $i++) {
            $frmData[$i] = $form->data['exportFields'][$i];
            $frmData[$i]['order'] = collect($frmData[$i]['order'])->flip()->merge($tags)->toArray();
        }
        $data['exportType'] = $form->data['exportType'];
        $data['frmData'] = $frmData;

        return $data;
    }

    /**
     * Choose export type and build data.
     *
     * @param array $fields
     * @return string|null
     * @throws \League\Csv\CannotInsertRecord
     */
    public function buildExport(array $fields)
    {
        if ($fields['exportDestination'] === 'geolocate') {
            return $this->geoLocateExportService->buildGeoLocateExport($fields);
        }

        return null;
    }

}