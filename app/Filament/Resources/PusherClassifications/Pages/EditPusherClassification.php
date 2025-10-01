<?php

namespace App\Filament\Resources\PusherClassifications\Pages;

use App\Filament\Resources\PusherClassifications\PusherClassificationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPusherClassification extends EditRecord
{
    protected static string $resource = PusherClassificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
