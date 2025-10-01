<?php

namespace App\Filament\Resources\EventTeams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventTeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('event_id')
                    ->relationship('event', 'title')
                    ->required(),
                TextInput::make('title'),
            ]);
    }
}
