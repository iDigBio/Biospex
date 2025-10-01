<?php

namespace App\Filament\Resources\TeamCategories\Pages;

use App\Filament\Resources\TeamCategories\TeamCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeamCategories extends ListRecords
{
    protected static string $resource = TeamCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
