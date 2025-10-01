<?php

namespace App\Filament\Resources\Metas\Pages;

use App\Filament\Resources\Metas\MetaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMeta extends ViewRecord
{
    protected static string $resource = MetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
