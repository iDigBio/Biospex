<?php

namespace App\Filament\Resources\Groups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('title'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
