<?php

namespace App\Filament\Resources\Headers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use ValentinMorice\FilamentJsonColumn\JsonColumn;

class HeaderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('project_id')
                    ->required()
                    ->numeric(),
                JsonColumn::make('header')
                    ->columnSpanFull(),
            ]);
    }
}
