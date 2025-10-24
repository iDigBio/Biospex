<?php

namespace App\Filament\Resources\Metas\Pages;

use App\Filament\Resources\Metas\MetaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMeta extends EditRecord
{
    protected static string $resource = MetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
