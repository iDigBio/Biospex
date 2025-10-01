<?php

namespace App\Filament\Resources\ActorExpeditions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActorExpeditionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('expedition.title')
                    ->label('Expedition'),
                TextEntry::make('actor.title')
                    ->label('Actor'),
                TextEntry::make('state')
                    ->numeric(),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('error')
                    ->numeric(),
                TextEntry::make('order')
                    ->numeric(),
                TextEntry::make('expert')
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
