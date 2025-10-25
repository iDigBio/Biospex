<?php

namespace App\Filament\Resources\PanoptesProjects\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PanoptesProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('project_id')
                    ->numeric(),
                TextInput::make('expedition_id')
                    ->numeric(),
                TextInput::make('panoptes_project_id')
                    ->numeric(),
                TextInput::make('panoptes_workflow_id')
                    ->required()
                    ->numeric(),
                Textarea::make('subject_sets')
                    ->columnSpanFull(),
                TextInput::make('slug'),
                TextInput::make('title')
                    ->required()
                    ->default('Notes From Nature'),
            ]);
    }
}
