<?php

namespace App\Filament\Resources\ExpeditionStats\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExpeditionStatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('expedition_id')
                    ->relationship('expedition', 'title')
                    ->required(),
                TextInput::make('local_subject_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('subject_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('transcriptions_goal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('local_transcriptions_completed')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('transcriptions_completed')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('transcriber_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('percent_completed')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
