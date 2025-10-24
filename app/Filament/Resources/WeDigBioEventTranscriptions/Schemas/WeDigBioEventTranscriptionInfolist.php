<?php

namespace App\Filament\Resources\WeDigBioEventTranscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WeDigBioEventTranscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('classification_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('project.title')
                    ->label('Project'),
                TextEntry::make('event_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
