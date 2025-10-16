<?php

namespace App\Filament\Resources\Imports\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ImportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('project_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('file')
                    ->placeholder('-'),
                IconEntry::make('error')
                    ->boolean(),
                TextEntry::make('processing')
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
