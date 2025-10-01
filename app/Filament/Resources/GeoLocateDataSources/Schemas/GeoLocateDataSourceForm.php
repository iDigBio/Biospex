<?php

namespace App\Filament\Resources\GeoLocateDataSources\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GeoLocateDataSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required(),
                Select::make('expedition_id')
                    ->relationship('expedition', 'title')
                    ->required(),
                Select::make('geo_locate_form_id')
                    ->relationship('geoLocateForm', 'name')
                    ->required(),
                Select::make('geo_locate_community_id')
                    ->relationship('geoLocateCommunity', 'name'),
                Select::make('download_id')
                    ->relationship('download', 'id'),
                TextInput::make('data_source'),
                TextInput::make('data'),
            ]);
    }
}
