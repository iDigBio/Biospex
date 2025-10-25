<?php

namespace App\Filament\Resources\TranscriptionLocations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TranscriptionLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('classification_id')
                    ->required()
                    ->numeric(),
                TextInput::make('project_id')
                    ->required()
                    ->numeric(),
                Select::make('expedition_id')
                    ->relationship('expedition', 'title')
                    ->required(),
                TextInput::make('state_county_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}
