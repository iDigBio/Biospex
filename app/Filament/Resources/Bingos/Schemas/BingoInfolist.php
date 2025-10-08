<?php

namespace App\Filament\Resources\Bingos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BingoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_name')
                    ->label('User Name')
                    ->getStateUsing(fn ($record) => $record->user?->getFilamentName()),
                TextEntry::make('project.title')
                    ->label('Project'),
                TextEntry::make('title'),
                TextEntry::make('directions'),
                TextEntry::make('contact'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
