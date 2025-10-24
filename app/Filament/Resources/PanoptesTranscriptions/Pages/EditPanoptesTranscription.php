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

namespace App\Filament\Resources\PanoptesTranscriptions\Pages;

use App\Filament\Resources\PanoptesTranscriptions\PanoptesTranscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPanoptesTranscription extends EditRecord
{
    protected static string $resource = PanoptesTranscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->convertObjectIdsToStrings($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['json_editor']) && is_string($data['json_editor'])) {
            try {
                $jsonData = json_decode($data['json_editor'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON format: '.json_last_error_msg());
                }

                return $jsonData;

            } catch (\Exception $e) {
                Notification::make()
                    ->title('JSON Error')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }

    private function convertObjectIdsToStrings(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_object($value) && get_class($value) === 'MongoDB\BSON\ObjectId') {
                $data[$key] = (string) $value;
            } elseif (is_array($value)) {
                $data[$key] = $this->convertObjectIdsToStrings($value);
            }
        }

        return $data;
    }
}
