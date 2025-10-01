<?php

namespace App\Filament\Resources\Metas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MetaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('project_id')
                    ->numeric(),
                TextEntry::make('xml'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
