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
                    ->numeric(),
                TextEntry::make('subject_count')
                    ->numeric(),
                TextEntry::make('transcriptions_goal')
                    ->numeric(),
                TextEntry::make('local_transcriptions_completed')
                    ->numeric(),
                TextEntry::make('transcriptions_completed')
                    ->numeric(),
                TextEntry::make('transcriber_count')
                    ->numeric(),
                TextEntry::make('percent_completed')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
