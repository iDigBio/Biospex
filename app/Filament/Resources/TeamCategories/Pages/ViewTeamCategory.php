<?php

namespace App\Filament\Resources\TeamCategories\Pages;

use App\Filament\Resources\TeamCategories\TeamCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTeamCategory extends ViewRecord
{
    protected static string $resource = TeamCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
