<?php

namespace App\Filament\Resources\WorkflowManagers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkflowManagerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('expedition_id')
                    ->relationship('expedition', 'title')
                    ->required(),
                Toggle::make('stopped')
                    ->default(false),
            ]);
    }
}
