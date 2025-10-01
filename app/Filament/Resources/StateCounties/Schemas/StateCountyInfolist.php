<?php

namespace App\Filament\Resources\StateCounties\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StateCountyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('county_name')
                    ->placeholder('-'),
                TextEntry::make('state_county')
                    ->placeholder('-'),
                TextEntry::make('state_abbr')
                    ->placeholder('-'),
                TextEntry::make('state_abbr_cap')
                    ->placeholder('-'),
                TextEntry::make('geometry')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('value')
                    ->placeholder('-'),
                TextEntry::make('geo_id')
                    ->placeholder('-'),
                TextEntry::make('geo_id_2')
                    ->placeholder('-'),
                TextEntry::make('geographic_name')
                    ->placeholder('-'),
                TextEntry::make('state_num')
                    ->placeholder('-'),
                TextEntry::make('county_num')
                    ->placeholder('-'),
                TextEntry::make('fips_forumla')
                    ->placeholder('-'),
                TextEntry::make('has_error')
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
