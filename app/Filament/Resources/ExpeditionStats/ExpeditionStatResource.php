<?php

namespace App\Filament\Resources\ExpeditionStats;

use App\Filament\Resources\ExpeditionStats\Pages\CreateExpeditionStat;
use App\Filament\Resources\ExpeditionStats\Pages\EditExpeditionStat;
use App\Filament\Resources\ExpeditionStats\Pages\ListExpeditionStats;
use App\Filament\Resources\ExpeditionStats\Pages\ViewExpeditionStat;
use App\Filament\Resources\ExpeditionStats\Schemas\ExpeditionStatForm;
use App\Filament\Resources\ExpeditionStats\Schemas\ExpeditionStatInfolist;
use App\Filament\Resources\ExpeditionStats\Tables\ExpeditionStatsTable;
use App\Models\ExpeditionStat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExpeditionStatResource extends Resource
{
    protected static ?string $model = ExpeditionStat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ExpeditionStatForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExpeditionStatInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpeditionStatsTable::configure($table);
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
            'index' => ListExpeditionStats::route('/'),
            'create' => CreateExpeditionStat::route('/create'),
            'view' => ViewExpeditionStat::route('/{record}'),
            'edit' => EditExpeditionStat::route('/{record}/edit'),
        ];
    }
}
