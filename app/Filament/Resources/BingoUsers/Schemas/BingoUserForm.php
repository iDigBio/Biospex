<?php

namespace App\Filament\Resources\BingoUsers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BingoUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bingo_id')
                    ->relationship('bingo', 'title')
                    ->required(),
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('ip')
                    ->required(),
                TextInput::make('latitude')
                    ->required()
                    ->numeric(),
                TextInput::make('longitude')
                    ->required()
                    ->numeric(),
                TextInput::make('city')
                    ->required(),
                Toggle::make('winner')
                    ->required(),
            ]);
    }
}
