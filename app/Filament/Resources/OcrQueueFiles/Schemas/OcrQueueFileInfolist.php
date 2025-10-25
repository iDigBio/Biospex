<?php

namespace App\Filament\Resources\OcrQueueFiles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OcrQueueFileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('queue_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('subject_id'),
                TextEntry::make('access_uri'),
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
