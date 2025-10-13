<?php

namespace App\Filament\Resources\GeoLocateForms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use ValentinMorice\FilamentJsonColumn\JsonColumn;

class GeoLocateFormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('group_id')
                    ->relationship('group', 'title')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                JsonColumn::make('fields')
                    ->required(),
            ]);
    }
}
