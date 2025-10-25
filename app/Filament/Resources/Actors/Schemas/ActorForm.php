<?php

namespace App\Filament\Resources\Actors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('url')
                    ->url()
                    ->required(),
                TextInput::make('class')
                    ->required(),
            ]);
    }
}
