<?php

namespace App\Filament\Resources\StateCounties\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StateCountyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('county_name'),
                TextInput::make('state_county'),
                TextInput::make('state_abbr'),
                TextInput::make('state_abbr_cap'),
                Textarea::make('geometry')
                    ->columnSpanFull(),
                TextInput::make('value'),
                TextInput::make('geo_id'),
                TextInput::make('geo_id_2'),
                TextInput::make('geographic_name'),
                TextInput::make('state_num'),
                TextInput::make('county_num'),
                TextInput::make('fips_forumla'),
                TextInput::make('has_error'),
            ]);
    }
}
