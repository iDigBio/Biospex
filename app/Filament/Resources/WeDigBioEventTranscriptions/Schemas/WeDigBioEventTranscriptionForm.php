<?php

namespace App\Filament\Resources\WeDigBioEventTranscriptions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WeDigBioEventTranscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('classification_id')
                    ->required()
                    ->numeric(),
                Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required(),
                TextInput::make('event_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}
