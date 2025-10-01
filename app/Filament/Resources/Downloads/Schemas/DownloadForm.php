<?php

namespace App\Filament\Resources\Downloads\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DownloadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('expedition_id')
                    ->relationship('expedition', 'title')
                    ->required(),
                Select::make('actor_id')
                    ->relationship('actor', 'title')
                    ->required(),
                TextInput::make('file')
                    ->required(),
                TextInput::make('type')
                    ->required(),
            ]);
    }
}
