<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('city'),
                TextEntry::make('latitude')
                    ->numeric(),
                TextEntry::make('longitude')
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
