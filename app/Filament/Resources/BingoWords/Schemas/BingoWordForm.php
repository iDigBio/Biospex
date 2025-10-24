<?php

namespace App\Filament\Resources\BingoWords\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BingoWordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bingo_id')
                    ->relationship('bingo', 'title')
                    ->required(),
                TextInput::make('word'),
                TextInput::make('definition'),
            ]);
    }
}
