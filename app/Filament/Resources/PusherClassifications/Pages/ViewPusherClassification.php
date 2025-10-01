<?php

namespace App\Filament\Resources\PusherClassifications\Pages;

use App\Filament\Resources\PusherClassifications\PusherClassificationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPusherClassification extends ViewRecord
{
    protected static string $resource = PusherClassificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
