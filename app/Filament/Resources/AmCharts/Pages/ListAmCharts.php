<?php

namespace App\Filament\Resources\AmCharts\Pages;

use App\Filament\Resources\AmCharts\AmChartResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAmCharts extends ListRecords
{
    protected static string $resource = AmChartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
