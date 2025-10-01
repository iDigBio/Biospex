<?php

namespace App\Filament\Resources\GeoLocateForms\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GeoLocateFormInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('group.title')
                    ->label('Group'),
                TextEntry::make('name'),
                TextEntry::make('hash'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
