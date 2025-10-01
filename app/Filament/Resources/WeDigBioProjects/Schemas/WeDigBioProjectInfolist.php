<?php

namespace App\Filament\Resources\WeDigBioProjects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WeDigBioProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('panoptes_project_id')
                    ->numeric(),
                TextEntry::make('panoptes_workflow_id')
                    ->numeric(),
                TextEntry::make('subject_sets')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
