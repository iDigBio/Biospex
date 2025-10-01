<?php

namespace App\Filament\Resources\FailedJobs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FailedJobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('connection')
                    ->columnSpanFull(),
                Textarea::make('queue')
                    ->columnSpanFull(),
                Textarea::make('payload')
                    ->columnSpanFull(),
                Textarea::make('exception')
                    ->columnSpanFull(),
                DateTimePicker::make('failed_at'),
            ]);
    }
}
