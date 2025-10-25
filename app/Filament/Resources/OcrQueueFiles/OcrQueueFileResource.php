<?php

namespace App\Filament\Resources\OcrQueueFiles;

use App\Filament\Resources\OcrQueueFiles\Pages\CreateOcrQueueFile;
use App\Filament\Resources\OcrQueueFiles\Pages\EditOcrQueueFile;
use App\Filament\Resources\OcrQueueFiles\Pages\ListOcrQueueFiles;
use App\Filament\Resources\OcrQueueFiles\Pages\ViewOcrQueueFile;
use App\Filament\Resources\OcrQueueFiles\Schemas\OcrQueueFileForm;
use App\Filament\Resources\OcrQueueFiles\Schemas\OcrQueueFileInfolist;
use App\Filament\Resources\OcrQueueFiles\Tables\OcrQueueFilesTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\OcrQueueFile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OcrQueueFileResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = OcrQueueFile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OcrQueueFileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OcrQueueFileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OcrQueueFilesTable::configure($table);
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
            'index' => ListOcrQueueFiles::route('/'),
            'create' => CreateOcrQueueFile::route('/create'),
            'view' => ViewOcrQueueFile::route('/{record}'),
            'edit' => EditOcrQueueFile::route('/{record}/edit'),
        ];
    }
}
