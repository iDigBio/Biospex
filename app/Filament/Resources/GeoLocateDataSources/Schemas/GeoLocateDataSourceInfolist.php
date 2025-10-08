<?php

namespace App\Filament\Resources\GeoLocateDataSources\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GeoLocateDataSourceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('project.title')
                    ->label('Project'),
                TextEntry::make('expedition.title')
                    ->label('Expedition'),
                TextEntry::make('geoLocateForm.name')
                    ->label('Geo locate form'),
                TextEntry::make('geoLocateCommunity.name')
                    ->label('Geo locate community')
                    ->placeholder('-'),
                TextEntry::make('download.id')
                    ->label('Download')
                    ->placeholder('-'),
                TextEntry::make('data_source')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
