<?php

namespace App\Filament\Resources\TeamCategories\Pages;

use App\Filament\Resources\TeamCategories\TeamCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTeamCategory extends EditRecord
{
    protected static string $resource = TeamCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
