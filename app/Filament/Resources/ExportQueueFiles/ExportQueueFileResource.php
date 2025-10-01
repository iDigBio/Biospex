<?php

namespace App\Filament\Resources\ExportQueueFiles;

use App\Filament\Resources\ExportQueueFiles\Pages\CreateExportQueueFile;
use App\Filament\Resources\ExportQueueFiles\Pages\EditExportQueueFile;
use App\Filament\Resources\ExportQueueFiles\Pages\ListExportQueueFiles;
use App\Filament\Resources\ExportQueueFiles\Pages\ViewExportQueueFile;
use App\Filament\Resources\ExportQueueFiles\Schemas\ExportQueueFileForm;
use App\Filament\Resources\ExportQueueFiles\Schemas\ExportQueueFileInfolist;
use App\Filament\Resources\ExportQueueFiles\Tables\ExportQueueFilesTable;
use App\Models\ExportQueueFile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExportQueueFileResource extends Resource
{
    protected static ?string $model = ExportQueueFile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ExportQueueFileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExportQueueFileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExportQueueFilesTable::configure($table);
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
            'index' => ListExportQueueFiles::route('/'),
            'create' => CreateExportQueueFile::route('/create'),
            'view' => ViewExportQueueFile::route('/{record}'),
            'edit' => EditExportQueueFile::route('/{record}/edit'),
        ];
    }
}
