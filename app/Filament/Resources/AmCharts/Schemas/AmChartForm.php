<?php

namespace App\Filament\Resources\AmCharts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AmChartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required(),
                TextInput::make('series'),
                TextInput::make('data'),
                TextInput::make('queued')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
