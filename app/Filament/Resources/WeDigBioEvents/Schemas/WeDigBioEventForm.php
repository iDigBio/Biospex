<?php

namespace App\Filament\Resources\WeDigBioEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WeDigBioEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('start_date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->required(),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}
