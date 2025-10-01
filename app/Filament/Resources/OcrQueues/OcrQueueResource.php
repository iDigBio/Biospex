<?php

namespace App\Filament\Resources\OcrQueues;

use App\Filament\Resources\OcrQueues\Pages\CreateOcrQueue;
use App\Filament\Resources\OcrQueues\Pages\EditOcrQueue;
use App\Filament\Resources\OcrQueues\Pages\ListOcrQueues;
use App\Filament\Resources\OcrQueues\Pages\ViewOcrQueue;
use App\Filament\Resources\OcrQueues\Schemas\OcrQueueForm;
use App\Filament\Resources\OcrQueues\Schemas\OcrQueueInfolist;
use App\Filament\Resources\OcrQueues\Tables\OcrQueuesTable;
use App\Models\OcrQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OcrQueueResource extends Resource
{
    protected static ?string $model = OcrQueue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OcrQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OcrQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OcrQueuesTable::configure($table);
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
            'index' => ListOcrQueues::route('/'),
            'create' => CreateOcrQueue::route('/create'),
            'view' => ViewOcrQueue::route('/{record}'),
            'edit' => EditOcrQueue::route('/{record}/edit'),
        ];
    }
}
