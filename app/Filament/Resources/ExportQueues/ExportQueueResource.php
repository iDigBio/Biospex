<?php

namespace App\Filament\Resources\ExportQueues;

use App\Filament\Resources\ExportQueues\Pages\CreateExportQueue;
use App\Filament\Resources\ExportQueues\Pages\EditExportQueue;
use App\Filament\Resources\ExportQueues\Pages\ListExportQueues;
use App\Filament\Resources\ExportQueues\Pages\ViewExportQueue;
use App\Filament\Resources\ExportQueues\Schemas\ExportQueueForm;
use App\Filament\Resources\ExportQueues\Schemas\ExportQueueInfolist;
use App\Filament\Resources\ExportQueues\Tables\ExportQueuesTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\ExportQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExportQueueResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = ExportQueue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ExportQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExportQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExportQueuesTable::configure($table);
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
            'index' => ListExportQueues::route('/'),
            'create' => CreateExportQueue::route('/create'),
            'view' => ViewExportQueue::route('/{record}'),
            'edit' => EditExportQueue::route('/{record}/edit'),
        ];
    }
}
