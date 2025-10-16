<?php

namespace App\Filament\Resources\TranscriptionLocations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TranscriptionLocationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('classification_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('project_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('expedition.title')
                    ->label('Expedition'),
                TextEntry::make('state_county_id')
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
