<?php

namespace App\Filament\Resources\BingoWords\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BingoWordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('bingo.title')
                    ->label('Bingo'),
                TextEntry::make('word')
                    ->placeholder('-'),
                TextEntry::make('definition')
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
