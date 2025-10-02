<?php

namespace App\Filament\Resources\AmCharts;

use App\Filament\Resources\AmCharts\Pages\CreateAmChart;
use App\Filament\Resources\AmCharts\Pages\EditAmChart;
use App\Filament\Resources\AmCharts\Pages\ListAmCharts;
use App\Filament\Resources\AmCharts\Pages\ViewAmChart;
use App\Filament\Resources\AmCharts\Schemas\AmChartForm;
use App\Filament\Resources\AmCharts\Schemas\AmChartInfolist;
use App\Filament\Resources\AmCharts\Tables\AmChartsTable;
use App\Models\AmChart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AmChartResource extends Resource
{
    protected static ?string $model = AmChart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AmChartForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AmChartInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmChartsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAmCharts::route('/'),
            'create' => CreateAmChart::route('/create'),
            'view' => ViewAmChart::route('/{record}'),
            'edit' => EditAmChart::route('/{record}/edit'),
        ];
    }
}
