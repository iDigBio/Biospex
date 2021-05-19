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

namespace App\Services\Export;

use App\Services\RapidServiceBase;
use App\Models\ExportForm;
use App\Models\User;
use FlashHelper;
use Illuminate\Support\Collection;
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
     * @var \App\Services\Export\RapidExportDbService
     */
    private $rapidExportDbService;

    /**
     * @var string
     */
    private $destination;

    /**
     * RapidExportService constructor.
     *
     * @param \App\Services\Export\RapidExportDbService $rapidExportDbService
     */
    public function __construct(
        RapidExportDbService $rapidExportDbService
    ) {
        $this->rapidExportDbService = $rapidExportDbService;
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
     * Build fields for version file creation.
     *
     * @return array
     */
    public function buildVersionFields(): array
    {
        $this->setDestination('generic');

        $columnTags = $this->getColumnTags();
        $header = $this->getLatestHeader();
        $tags = $this->mapColumns($header, $columnTags);

        return [
            "entries"           => "1",
            "exportDestination" => "generic",
            "exportType"        => "csv",
            "exportFields"      => [$tags->toArray()]
        ];
    }

    /**
     * Get mapped fields for export.
     *
     * @param array $data
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getMappedFields(array $data): array
    {
        return isset($data['exportFields']) ? $this->mapExportFields($data) : $this->mapDirectFields($data);
    }

    /**
     * Map the posted export order data.
     *
     * @param array $data
     * @return array
     */
    private function mapExportFields(array $data): array
    {
        $this->setDestination($data['exportDestination']);

        $data['exportFields'] = collect($data['exportFields'])->map(function ($array) {
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
     * Map direct export fields.
     *
     * @param array $data
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function mapDirectFields(array $data): array
    {
        $this->setDestination($data['exportDestination']);

        $fields = $this->getDestinationFieldFile($data['exportDestination']);
        $header = $this->getLatestHeader();
        $tags = $this->getColumnTags();

        $data['exportFields'] = collect($fields)->map(function ($field) use ($tags, $header) {
            return collect($tags)->map(function ($tag) use ($field) {
                return $field.$tag;
            })->filter(function ($tagged) use ($header) {
                return collect($header)->contains($tagged);
            });
        })->flatten()->toArray();

        return $data;
    }

    /**
     * Find rapid form by id.
     *
     * @param int $id
     * @return \App\Models\ExportForm
     */
    public function findFormById(int $id): ExportForm
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
     * @return string
     * @throws \App\Exceptions\PresenterException
     */
    public function createFileName(ExportForm $form, User $user, array &$fields): string
    {
        $exportExtensions = $this->getExportExtensions();

        $user = explode('@', $user->email);
        $form->file = $form->present()->form_name.'_'.$user[0].$exportExtensions[$fields['exportType']];
        $form->save();

        return $form->file;
    }

    /**
     * Get forms by destination.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFormsSelect(): Collection
    {
        $forms = $this->rapidExportDbService->getRapidFormsSelect();

        return $forms->mapToGroups(function ($item, $index) {
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
    public function getForm(string $destination, int $id = null): array
    {
        $this->setDestination($destination);

        return $id === null ? $this->newForm() : $this->existingForm($id);
    }

    /**
     * Return form for selected destination.
     *
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function newForm(): array
    {
        $header = $this->getLatestHeader();
        $columnTags = $this->getColumnTags();
        $tags = $this->mapColumns($header, $columnTags);

        return [
            'count'      => old('entries', 1),
            'exportType' => null,
            'fields'     => $this->destination === 'generic' ? null : $this->getDestinationFieldFile($this->destination),
            'tags'       => $tags,
            'frmData'    => null,
        ];
    }

    /**
     * Return form from existing form.
     *
     * @param int $id
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function existingForm(int $id): array
    {
        $form = $this->rapidExportDbService->findRapidFormById($id);
        $header = $this->getLatestHeader();
        $columnTags = $this->getColumnTags();
        $tags = $this->mapColumns($header, $columnTags);

        $frmData = null;
        for ($i = 0; $i < $form->data['entries']; $i++) {
            $frmData[$i] = $form->data['exportFields'][$i];
            $frmData[$i]['order'] = collect($frmData[$i]['order'])->flip()->merge($tags)->toArray();
        }

        return [
            'count'      => $form->data['entries'],
            'exportType' => $form->data['exportType'],
            'fields'     => $this->destination === 'generic' ? null : $this->getDestinationFieldFile($this->destination),
            'tags'       => $tags,
            'frmData'    => $frmData,
            'frmName'    => base64_encode($form->file),
            'frmId'      => $form->id,
        ];
    }

    /**
     * Set reserved columns according to destination.
     */
    public function getReservedColumns()
    {
        $reserved = $this->getConfigReservedColumns();

        return $reserved[$this->destination];
    }

    /**
     * Get latest header.
     *
     * @return array
     */
    public function getLatestHeader(): array
    {
        return $this->rapidExportDbService->getLatestHeader();
    }

    /**
     * Return last header id.
     *
     * @return int
     */
    public function getHeaderId(): int
    {
        return $this->rapidExportDbService->getHeaderId();
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
                FlashHelper::warning(t('The export you would like to delete does not exist.'));

                return;
            }

            if ($form->user_id !== $userId) {
                FlashHelper::warning(t('You do not have sufficient permissions.'));

                return;
            }

            if (! Storage::exists(config('config.rapid_export_dir').'/'.$form->file)) {
                FlashHelper::warning(t('RAPID export file does not exist.'));

                return;
            }

            Storage::delete(config('config.rapid_export_dir').'/'.$form->file);
            $form->delete();

            FlashHelper::success(t('RAPID export file and data has been deleted.'));

            return;
        } catch (\Exception $exception) {
            FlashHelper::warning($exception->getMessage());

            return;
        }
    }

    /**
     * Created rapid version record.
     *
     * @param array $attributes
     */
    public function createVersionRecord(array $attributes)
    {
        $this->rapidExportDbService->createVersionRecord($attributes);
    }
}