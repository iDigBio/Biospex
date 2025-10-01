<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required(),
                Select::make('owner_id')
                    ->relationship('owner', 'id')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('description')
                    ->required(),
                TextInput::make('hashtag'),
                TextInput::make('contact')
                    ->required(),
                TextInput::make('contact_email')
                    ->email()
                    ->required(),
                DateTimePicker::make('start_date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->required(),
                TextInput::make('timezone')
                    ->required(),
            ]);
    }
}
