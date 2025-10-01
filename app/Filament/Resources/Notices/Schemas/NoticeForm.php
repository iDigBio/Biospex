<?php

namespace App\Filament\Resources\Notices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NoticeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('message')
                    ->required(),
                TextInput::make('enabled')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
