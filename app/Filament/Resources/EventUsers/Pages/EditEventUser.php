<?php

namespace App\Filament\Resources\EventUsers\Pages;

use App\Filament\Resources\EventUsers\EventUserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEventUser extends EditRecord
{
    protected static string $resource = EventUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
