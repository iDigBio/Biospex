<?php

namespace App\Filament\Resources\Downloads\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DownloadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('expedition.title')
                    ->label('Expedition'),
                TextEntry::make('actor.title')
                    ->label('Actor'),
                TextEntry::make('file')
                    ->url(fn ($record) => $record->present()->downloadType())
                    ->openUrlInNewTab(),
                TextEntry::make('type'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
