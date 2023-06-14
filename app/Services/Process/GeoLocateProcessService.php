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

namespace App\Services\Process;

use App\Models\Expedition;
use App\Models\GeoLocateForm;
use App\Models\Header;
use App\Models\Project;
use App\Repositories\ExpeditionRepository;
use App\Repositories\GeoLocateFormRepository;
use App\Repositories\HeaderRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class GeoLocateProcessService
{

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepository;

    /**
     * @var \App\Repositories\GeoLocateFormRepository
     */
    private GeoLocateFormRepository $geoLocateFormRepository;

    /**
     * @var \App\Repositories\HeaderRepository
     */
    private HeaderRepository $headerRepository;

    /**
     * Construct.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepository
     * @param \App\Repositories\GeoLocateFormRepository $geoLocateFormRepository
     * @param \App\Repositories\HeaderRepository $headerRepository
     */
    public function __construct(
        ExpeditionRepository $expeditionRepository,
        GeoLocateFormRepository $geoLocateFormRepository,
        HeaderRepository $headerRepository
    ) {

        $this->expeditionRepository = $expeditionRepository;
        $this->geoLocateFormRepository = $geoLocateFormRepository;
        $this->headerRepository = $headerRepository;
    }

    /**
     * Find project with relations.
     *
     * @param int $expeditionId
     * @param array $relations
     * @return \App\Models\Expedition
     */
    public function findExpeditionWithRelations(int $expeditionId, array $relations = []): Expedition
    {
        return $this->expeditionRepository->findWith($expeditionId, $relations);
    }

    /**
     * Get form by expedition id.
     *
     * @param int $expeditionId
     * @return \App\Models\GeoLocateForm|null
     */
    public function getFormByExpeditionId(int $expeditionId): ?GeoLocateForm
    {
        return $this->geoLocateFormRepository->findBy('expedition_id', $expeditionId);
    }

    /**
     * Get the form based on new or existing.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getForm(int $projectId, int $expeditionId = null): array
    {
        $form = $this->getFormByExpeditionId($expeditionId);

        return $form === null ? $this->newForm($projectId) : $this->existingForm($form, $projectId, $expeditionId);
    }

    /**
     * Return form for selected destination.
     *
     * @param int $projectId
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function newForm(int $projectId): array
    {
        $record = $this->getHeader($projectId);

        return [
            'count'      => old('entries', 1),
            'sourceType' => null,
            'fields'     => $this->getGeoLocateFields(),
            'header'     => $record->header,
            'frmData'    => null,
        ];
    }

    /**
     * Return form from existing form.
     *
     * @param \App\Models\GeoLocateForm $form
     * @param int $projectId
     * @param int $expeditionId
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function existingForm(GeoLocateForm $form, int $projectId, int $expeditionId): array
    {
        $record = $this->getHeader($projectId);

        $frmData = null;
        for ($i = 0; $i < $form->data['entries']; $i++) {
            $frmData[$i] = $form->data['exportFields'][$i];
            $frmData[$i]['order'] = collect($frmData[$i]['order'])->flip()->merge($record->header)->toArray();
        }

        return [
            'count'      => $form->data['entries'],
            'sourceType' => $form->data['sourceType'],
            'fields'     => $this->getGeoLocateFields(),
            'header'     => $record->header,
            'frmData'    => $frmData,
            'frmName'    => base64_encode($form->file),
            'frmId'      => $form->id,
        ];
    }

    /**
     * Find project header for subjects.
     *
     * @param int $projectId
     * @return \App\Models\Header
     */
    public function getHeader(int $projectId): Header
    {
        return $this->headerRepository->findBy('project_id', $projectId);
    }

    /**
     * Save the export form data.
     *
     * @param array $fields
     * @param int $expeditionId
     * @return \App\Models\GeoLocateForm
     */
    public function saveForm(array $fields, int $expeditionId): GeoLocateForm
    {
        $data = [
            'expedition_id' => $expeditionId,
            'file'          => md5($expeditionId).'.csv',
            'properties'    => $fields,
        ];

        return $this->geoLocateFormRepository->create($data);
    }

    /**
     * Map header columns to tags.
     *
     * @param array $header
     * @param array $tags
     * @return \Illuminate\Support\Collection
     */
    public function mapColumns(array $header, array $tags): Collection
    {
        return collect($header)->mapToGroups(function ($value) use ($tags) {
            foreach ($tags as $tag) {
                if (preg_match('/'.$tag.'/', $value, $matches)) {
                    return [$matches[0] => $value];
                }
            }

            return ['unused' => $value];
        })->forget('unused')->map(function ($value, $key) {
            return $value->sort()->values();
        });
    }

    /**
     * Get GeoLocate fields from file.
     *
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getGeoLocateFields(): array
    {
        return json_decode(File::get(config('config.geolocate_fields_file')), true);
    }

    /**
     * Map the posted geolocate form order data.
     *
     * @param array $data
     * @return array
     */
    public function mapExportFields(array $data): array
    {
        $data['exportFields'] = collect($data['exportFields'])->map(function ($array) {
            return collect($array)->map(function ($item, $key) {
                if ($key === 'order') {
                    return $item === null ? null : explode(',', $item);
                }

                return $item;
            });
        })->toArray();

        unset($data['_token']);

        return $data;
    }
}