<?php

namespace App\Filament\Resources\ExportQueueFiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExportQueueFileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('queue_id')
                    ->relationship('queue', 'id')
                    ->required(),
                TextInput::make('subject_id')
                    ->required(),
                TextInput::make('access_uri')
                    ->required(),
                TextInput::make('message'),
                Toggle::make('processed')
                    ->required(),
                TextInput::make('tries')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
