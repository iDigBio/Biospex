<?php

namespace App\Filament\Resources\ActorContacts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActorContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('actor.title')
                    ->label('Actor'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
