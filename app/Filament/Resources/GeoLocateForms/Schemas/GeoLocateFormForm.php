<?php

namespace App\Filament\Resources\GeoLocateForms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                TextInput::make('hash')
                    ->required(),
                TextInput::make('fields')
                    ->required(),
            ]);
    }
}
