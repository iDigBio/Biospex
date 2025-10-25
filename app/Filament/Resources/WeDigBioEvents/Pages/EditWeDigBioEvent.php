<?php

namespace App\Filament\Resources\WeDigBioEvents\Pages;

use App\Filament\Resources\WeDigBioEvents\WeDigBioEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWeDigBioEvent extends EditRecord
{
    protected static string $resource = WeDigBioEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
