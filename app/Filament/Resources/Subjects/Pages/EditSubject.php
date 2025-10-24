<?php

namespace App\Filament\Resources\Subjects\Pages;

use App\Filament\Resources\Subjects\SubjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSubject extends EditRecord
{
    protected static string $resource = SubjectResource::class;

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

                // Use the JSON data as the complete document
                return $jsonData;

            } catch (\Exception $e) {
                Notification::make()
                    ->title('JSON Error')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();

                // Prevent saving if JSON is invalid
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
