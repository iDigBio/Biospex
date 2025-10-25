<?php

namespace App\Filament\Resources\TeamCategories\Pages;

use App\Filament\Resources\TeamCategories\TeamCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeamCategory extends CreateRecord
{
    protected static string $resource = TeamCategoryResource::class;
}
