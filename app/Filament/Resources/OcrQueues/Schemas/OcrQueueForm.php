<?php

namespace App\Filament\Resources\OcrQueues\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OcrQueueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required(),
                TextInput::make('expedition_id')
                    ->numeric(),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('error')
                    ->required(),
            ]);
    }
}
