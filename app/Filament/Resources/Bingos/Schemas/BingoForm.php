<?php

namespace App\Filament\Resources\Bingos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BingoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('directions')
                    ->required(),
                TextInput::make('contact')
                    ->required(),
            ]);
    }
}
