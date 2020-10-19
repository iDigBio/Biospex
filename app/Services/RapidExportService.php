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
use Exception;
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
     * @var \App\Services\RapidFileService
     */
    private $rapidFileService;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * @var \App\Services\CsvService
     */
    private $csvService;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var array
     */
    private $reserved;

    /**
     * RapidExportService constructor.
     *
     * @param \App\Services\RapidExportDbService $rapidExportDbService
     * @param \App\Services\RapidFileService $rapidFileService
     * @param \App\Services\MongoDbService $mongoDbService
     * @param \App\Services\CsvService $csvService
     */
    public function __construct(
        RapidExportDbService $rapidExportDbService,
        RapidFileService $rapidFileService,
        MongoDbService $mongoDbService, CsvService $csvService
    )
    {
        $this->rapidExportDbService = $rapidExportDbService;
        $this->rapidFileService = $rapidFileService;
        $this->mongoDbService = $mongoDbService;
        $this->csvService = $csvService;
    }

    /**
     * Set destination.
     *
     * @param string $destination
     */
    public function setDestination(string $destination)
    {
        $this->destination = $destination;
    }

    /**
     * Set reserved columns according to destination.
     */
    public function setReservedColumns()
    {
        $reserved = $this->rapidFileService->getReservedColumns();
        $this->reserved = $reserved[$this->destination];
    }

    /**
     * Get cursor for rapid documents
     * @return mixed
     */
    public function getRapidRecordsCursor()
    {
        $this->mongoDbService->setCollection('rapid_records');

        $cursor = $this->mongoDbService->find();
        $cursor->setTypeMap([
            'array'    => 'array',
            'document' => 'array',
            'root'     => 'array',
        ]);

        return $cursor;
    }

    /**
     * Build csv data for GeoLocate export.
     *
     * @param array $fields
     * @return array
     */
    public function buildCsvData(array $fields): array
    {
        $cursor = $this->getRapidRecordsCursor();

        $csvData = [];

        foreach ($cursor as $doc) {
            $csvData[] = $fields['direct'] ? $this->setDirectColumns($doc, $fields) : $this->setFormColumns($doc, $fields);
        }

        return $csvData;
    }

    /**
     * Set column headers and data according to what was selected.
     *
     * @param $doc
     * @param $fields
     * @return mixed
     */
    public function setFormColumns($doc, $fields)
    {
        $data = $this->buildReservedColumns($doc);

        foreach ($fields['exportFields'] as $fieldArray) {
            
            $field = $fieldArray['field'];
            $data[$field] = '';

            // unset to make foreach easier to deal with
            unset($fieldArray['field'], $fieldArray['order']);

            // indexes are the tags. isset skips index values that are null
            foreach ($fieldArray as $index => $value) {
                if (isset($fieldArray[$index]) && !empty($doc[$value])) {
                    $data[$field] = $doc[$value];
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Set array for export fields.
     *
     * @param $doc
     * @param $fields
     * @return array
     */
    public function setDirectColumns($doc, $fields):array
    {
        $data = $this->buildReservedColumns($doc);

        foreach ($fields['exportFields'] as $field) {
            if (isset($doc[$field])) {
                $data[$field] = $doc[$field];
            }
        }

        return $data;
    }

    /**
     * Set reserved columns.
     *
     * @param $doc
     * @return array
     */
    private function buildReservedColumns($doc)
    {
        $data = [];
        foreach ($this->reserved as $column => $item) {
            $data[$column] = (string) $doc[$item];
        }

        return $data;
    }

    /**
     * Build csv file and return it.
     *
     * @param array $csvData
     * @param string $frmFile
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    public function buildCsvFile(array $csvData, string $frmFile): string
    {
        $header = array_keys($csvData[0]);

        $file = Storage::path(config('config.rapid_export_dir').'/'.$frmFile);
        $this->csvService->writerCreateFromPath($file);
        $this->csvService->writer->addFormatter($this->csvService->setEncoding());

        $this->csvService->insertOne($header);
        $this->csvService->insertAll($csvData);

        return route('admin.download.export', ['file' => base64_encode($frmFile)]);
    }

    /**
     * Map the posted export order data.
     *
     * @param array $data
     * @return array
     */
    public function mapFormFields(array $data): array
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
        $data['direct'] = false;

        return $data;
    }

    /**
     * Map direct export fields.
     *
     * @param array $data
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function mapDirectFields(array $data): array
    {
        $fields = $this->rapidFileService->getDestinationFieldFile($data['exportDestination']);
        $header = $this->rapidFileService->getHeader();
        $tags = $this->rapidFileService->getColumnTags();

        $data['exportFields'] = collect($fields)->map(function($field) use($tags, $header) {
            return collect($tags)->map(function($tag) use($field){
                return $field . $tag;
            })->filter(function($tagged) use($header){
                return collect($header)->contains($tagged);
            });
        })->flatten()->toArray();

        $data['direct'] = true;

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
        $exportExtensions = $this->rapidFileService->getExportExtensions();

        $user = explode('@', $user->email);
        $form->file = $fields['frmFile'] = $form->present()->form_name . '_' . $user[0] . $exportExtensions[$fields['exportType']];
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
        $headers = collect($this->rapidFileService->getHeader());
        $columnTags = $this->rapidFileService->getColumnTags();
        $tags = $this->mapColumns($headers, $columnTags);

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
        $headers = collect($this->rapidFileService->getHeader());
        $columnTags = $this->rapidFileService->getColumnTags();
        $tags = $this->mapColumns($headers, $columnTags);

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
        $this->setDestination($fields['exportDestination']);
        $this->setReservedColumns();

        if ($fields['exportType'] === 'csv') {
            $csvData = $this->buildCsvData($fields);
            if (!isset($csvData[0])) {
                throw new Exception(t('Csv data returned empty while exporting.'));
            }

            return $this->buildCsvFile($csvData, $fields['frmFile']);
        }

        return null;
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