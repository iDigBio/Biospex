<?php

namespace App\Filament\Resources\Notices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NoticeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('message'),
                TextEntry::make('enabled')
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
