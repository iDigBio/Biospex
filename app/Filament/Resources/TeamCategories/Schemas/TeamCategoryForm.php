<?php

namespace App\Filament\Resources\TeamCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeamCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
