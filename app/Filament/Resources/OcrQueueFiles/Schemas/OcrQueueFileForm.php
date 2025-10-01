<?php

namespace App\Filament\Resources\OcrQueueFiles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OcrQueueFileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('queue_id')
                    ->required()
                    ->numeric(),
                TextInput::make('subject_id')
                    ->required(),
                TextInput::make('access_uri')
                    ->required(),
                Toggle::make('processed')
                    ->required(),
                TextInput::make('tries')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
