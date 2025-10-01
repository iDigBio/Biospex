<?php

namespace App\Filament\Resources\ActorWorkflows\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActorWorkflowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('workflow_id')
                    ->required()
                    ->numeric(),
                TextInput::make('actor_id')
                    ->required()
                    ->numeric(),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
