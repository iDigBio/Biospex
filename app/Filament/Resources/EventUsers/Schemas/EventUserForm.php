<?php

namespace App\Filament\Resources\EventUsers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nfn_user')
                    ->required(),
            ]);
    }
}
