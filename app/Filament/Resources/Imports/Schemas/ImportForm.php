<?php

namespace App\Filament\Resources\Imports\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ImportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('project_id')
                    ->required()
                    ->numeric(),
                TextInput::make('file'),
                Toggle::make('error')
                    ->required(),
                TextInput::make('processing')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
