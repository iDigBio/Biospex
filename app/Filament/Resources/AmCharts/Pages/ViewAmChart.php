<?php

namespace App\Filament\Resources\AmCharts\Pages;

use App\Filament\Resources\AmCharts\AmChartResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAmChart extends ViewRecord
{
    protected static string $resource = AmChartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
