<?php

namespace App\Filament\Resources\GeoLocateCommunities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GeoLocateCommunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('data')
                    ->required(),
            ]);
    }
}
