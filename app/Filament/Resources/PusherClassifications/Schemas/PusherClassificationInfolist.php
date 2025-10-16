<?php

namespace App\Filament\Resources\PusherClassifications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PusherClassificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('classification_id')
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
