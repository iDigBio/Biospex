<?php

namespace App\Filament\Resources\FailedJobs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FailedJobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('connection')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('queue')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('payload')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('exception')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('failed_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
