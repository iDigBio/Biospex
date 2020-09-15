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

use App\Repositories\Interfaces\ExportForm;
use App\Repositories\Interfaces\RapidHeader;
use Storage;

class RapidExportService extends RapidServiceBase
{
    /**
     * @var \App\Repositories\Interfaces\RapidHeader
     */
    private $rapidHeaderInterface;

    /**
     * @var \App\Repositories\Interfaces\ExportForm
     */
    private $exportFormInterface;


    /**
     * RapidExportService constructor.
     *
     * @param \App\Repositories\Interfaces\RapidHeader $rapidHeaderInterface
     * @param \App\Repositories\Interfaces\ExportForm $exportFormInterface
     */
    public function __construct(
        RapidHeader $rapidHeaderInterface,
        ExportForm $exportFormInterface
    )
    {
        $this->rapidHeaderInterface = $rapidHeaderInterface;
        $this->exportFormInterface = $exportFormInterface;
    }

    /**
     * Get header.
     *
     * @return mixed
     */
    public function getHeader()
    {
        $protected = config('config.protectedFields');

        $rapidHeader = $this->rapidHeaderInterface->first();

        return collect($rapidHeader->header)->reject(function ($field) use ($protected) {
            return in_array($field, $protected);
        });
    }

    /**
     * Map the posted export form data.
     *
     * @param array $data
     * @return array
     */
    public function mapExportFields(array $data): array
    {
        $fields = collect($data)->recursive();
        $fields->forget(['_token']);

        return $fields->map(function ($item, $key) {
            if ($key !== 'exportFields') {
                return $item;
            }

            return $item->map(function ($array) {
                if ($array['order'] === null) {
                    $array['order'] = null;

                    return $array;
                }

                $array['order'] = explode(',', $array['order']);

                return $array;
            });
        })->toArray();
    }

    /**
     * Safe the export form data.
     *
     * @param array $fields
     * @return \App\Models\ExportForm
     */
    public function saveForm(array $fields): \App\Models\ExportForm
    {
        $data = [
            'destination' => $fields['exportDestination'],
            'data'        => $fields,
        ];

        return $this->exportFormInterface->create($data);
    }

    /**
     * Get forms by destination.
     *
     * @param string $destination
     * @return \Illuminate\Support\Collection
     */
    public function getFormsByDestination(string $destination)
    {
        return $this->exportFormInterface->getFormsByDestination($destination);
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
        $form = $id === null ? null : $this->exportFormInterface->find($id);

        $count = $form === null ? old('entries', 1) : $form->data['entries'];
        $geoLocateFields = json_decode(Storage::get(config('config.geoLocateFields')), true);
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
}