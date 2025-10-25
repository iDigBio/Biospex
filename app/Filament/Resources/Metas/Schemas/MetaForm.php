<?php

namespace App\Filament\Resources\Metas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MetaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('project_id')
                    ->required()
                    ->numeric(),
                TextInput::make('xml')
                    ->required(),
            ]);
    }
}
