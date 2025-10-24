<?php

namespace App\Filament\Resources\AmCharts\Pages;

use App\Filament\Resources\AmCharts\AmChartResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAmChart extends EditRecord
{
    protected static string $resource = AmChartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
