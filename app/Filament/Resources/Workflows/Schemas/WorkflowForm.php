<?php

namespace App\Filament\Resources\Workflows\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkflowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('enabled')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
