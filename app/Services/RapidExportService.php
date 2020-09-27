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
use App\Models\User;
use Flash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Class RapidExportService
 *
 * @package App\Services
 */
class RapidExportService extends RapidServiceBase
{
    /**
     * @var \App\Services\RapidExportDbService
     */
    private $rapidExportDbService;

    /**
     * @var \App\Services\ExportService
     */
    private $exportService;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $exportExtensions;

    /**
     * RapidExportService constructor.
     *
     * @param \App\Services\RapidExportDbService $rapidExportDbService
     * @param \App\Services\ExportService $exportService
     */
    public function __construct(
        RapidExportDbService $rapidExportDbService,
        ExportService $exportService
    )
    {
        $this->rapidExportDbService = $rapidExportDbService;
        $this->exportService = $exportService;
        $this->exportExtensions = config('config.export_extensions');
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
     * Find rapid form by id.
     *
     * @param int $id
     * @return \App\Models\ExportForm
     */
    public function findFormById(int $id)
    {
        return $this->rapidExportDbService->findRapidFormById($id);
    }

    /**
     * Save the export form data.
     *
     * @param array $fields
     * @param int $userId
     * @return \App\Models\ExportForm
     */
    public function saveForm(array $fields, int $userId): ExportForm
    {
        return $this->rapidExportDbService->saveRapidForm($fields, $userId);
    }

    /**
     * Create form name using user and form data.
     *
     * @param \App\Models\ExportForm $form
     * @param \App\Models\User $user
     * @param array $fields
     */
    public function createFileName(ExportForm $form, User $user, array &$fields)
    {
        $user = explode('@', $user->email);
        $form->file = $fields['frmFile'] = $form->present()->form_name . '_' . $user[0] . $this->exportExtensions[$fields['exportType']];
        $form->save();
    }

    /**
     * Get forms by destination.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFormsSelect()
    {
        $forms = $this->rapidExportDbService->getRapidFormsSelect();

        return $forms->mapToGroups(function($item, $index){
            return [$item->destination => $item];
        });
    }

    /**
     * Get the form based on new or existing.
     *
     * @param string $destination
     * @param int|null $id
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getForm(string $destination, int $id = null)
    {
        return $id === null ? $this->newForm($destination) : $this->existingForm($destination, $id);
    }

    /**
     * Return form for selected destination.
     *
     * @param string $destination
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function newForm(string $destination )
    {
        $headers = $this->getHeader();
        $tags = $this->mapColumns($headers);

        return [
            'count' => old('entries', 1),
            'exportType' => null,
            'fields' => $this->getFields($destination),
            'tags' => $tags,
            'frmData' => null
        ];
    }

    /**
     * Return form from existing form.
     *
     * @param string $destination
     * @param int $id
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function existingForm(string $destination, int $id)
    {
        $form = $this->rapidExportDbService->findRapidFormById($id);
        $headers = $this->getHeader();
        $tags = $this->mapColumns($headers);

        $frmData = null;
        for($i=0; $i < $form->data['entries']; $i++) {
            $frmData[$i] = $form->data['exportFields'][$i];
            $frmData[$i]['order'] = collect($frmData[$i]['order'])->flip()->merge($tags)->toArray();
        }

        return [
            'count' => $form->data['entries'],
            'exportType' => $form->data['exportType'],
            'fields' => $this->getFields($destination),
            'tags' => $tags,
            'frmData' => $frmData,
            'frmName' => base64_encode($form->file),
            'frmId' => $form->id
        ];
    }

    /**
     * Get fields according to destination.
     *
     * @param string $destination
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getFields(string $destination): array
    {
        return json_decode(File::get(config('config.'.$destination.'_fields_file')), true);
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
        $this->exportService->setDestination($fields['exportDestination']);
        $this->exportService->setReservedColumns();

        return $this->exportService->buildExport($fields);
    }

    /**
     * Delete export.
     *
     * @param \App\Models\ExportForm $form
     * @param int $userId
     */
    public function deleteExport(ExportForm $form, int $userId)
    {
        try {
            if ($form === null) {
                Flash::warning(t('The export you would like to delete does not exist.'));

                return;
            }

            if ($form->user_id !== $userId) {
                Flash::warning(t('You do not have sufficient permissions.'));

                return;
            }

            if(! Storage::exists(config('config.rapid_export_dir') . '/' . $form->file)) {
                Flash::warning( t('RAPID export file does not exist.'));

                return;
            }

            Storage::delete(config('config.rapid_export_dir') . '/' . $form->file);
            $form->delete();

            Flash::success( t('RAPID export file and data has been deleted.'));

            return;
        }
        catch(\Exception $exception) {
            Flash::warning( $exception->getMessage());

            return;
        }
    }

}