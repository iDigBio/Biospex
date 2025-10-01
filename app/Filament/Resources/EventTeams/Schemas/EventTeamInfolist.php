<?php

namespace App\Filament\Resources\EventTeams\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventTeamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('event.title')
                    ->label('Event'),
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
