<?php

namespace App\Filament\Resources\ExportQueueFiles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExportQueueFileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('queue.id')
                    ->label('Queue'),
                TextEntry::make('subject_id'),
                TextEntry::make('access_uri'),
                TextEntry::make('message')
                    ->placeholder('-'),
                IconEntry::make('processed')
                    ->boolean(),
                TextEntry::make('tries')
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
