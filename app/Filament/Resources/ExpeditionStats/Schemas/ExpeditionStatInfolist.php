<?php

namespace App\Filament\Resources\ExpeditionStats\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExpeditionStatInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('expedition.title')
                    ->label('Expedition'),
                TextEntry::make('local_subject_count')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('subject_count')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('transcriptions_goal')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('local_transcriptions_completed')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('transcriptions_completed')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('transcriber_count')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('percent_completed')
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
