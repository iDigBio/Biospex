<?php

namespace App\Filament\Resources\PusherClassifications\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PusherClassificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('classification_id')
                    ->required()
                    ->numeric(),
                TextInput::make('data')
                    ->required(),
            ]);
    }
}
