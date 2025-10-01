<?php

namespace App\Filament\Resources\EventTranscriptions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventTranscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('classification_id')
                    ->required()
                    ->numeric(),
                Select::make('event_id')
                    ->relationship('event', 'title')
                    ->required(),
                Select::make('team_id')
                    ->relationship('team', 'title')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
            ]);
    }
}
