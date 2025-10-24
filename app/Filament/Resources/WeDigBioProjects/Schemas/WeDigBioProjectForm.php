<?php

namespace App\Filament\Resources\WeDigBioProjects\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WeDigBioProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('panoptes_project_id')
                    ->required()
                    ->numeric(),
                TextInput::make('panoptes_workflow_id')
                    ->required()
                    ->numeric(),
                Textarea::make('subject_sets')
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->required()
                    ->default('Notes From Nature'),
            ]);
    }
}
