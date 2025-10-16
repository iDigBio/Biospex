<?php

namespace App\Filament\Resources\PanoptesProjects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PanoptesProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('project_id')
                    ->numeric(thousandsSeparator: '')
                    ->placeholder('-'),
                TextEntry::make('expedition_id')
                    ->numeric(thousandsSeparator: '')
                    ->placeholder('-'),
                TextEntry::make('panoptes_project_id')
                    ->numeric(thousandsSeparator: '')
                    ->placeholder('-'),
                TextEntry::make('panoptes_workflow_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('subject_sets')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('slug')
                    ->placeholder('-'),
                TextEntry::make('title'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
