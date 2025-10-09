<?php

namespace App\Filament\Resources\EventTranscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventTranscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('classification_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('event.title')
                    ->label('Event'),
                TextEntry::make('team.title')
                    ->label('Team'),
                TextEntry::make('user.nfn_user')
                    ->label('User'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
