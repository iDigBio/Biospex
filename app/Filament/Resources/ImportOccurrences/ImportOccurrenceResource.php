<?php

namespace App\Filament\Resources\ImportOccurrences;

use App\Filament\Resources\ImportOccurrences\Pages\CreateImportOccurrence;
use App\Filament\Resources\ImportOccurrences\Pages\EditImportOccurrence;
use App\Filament\Resources\ImportOccurrences\Pages\ListImportOccurrences;
use App\Filament\Resources\ImportOccurrences\Pages\ViewImportOccurrence;
use App\Filament\Resources\ImportOccurrences\Schemas\ImportOccurrenceForm;
use App\Filament\Resources\ImportOccurrences\Schemas\ImportOccurrenceInfolist;
use App\Filament\Resources\ImportOccurrences\Tables\ImportOccurrencesTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\ImportOccurrence;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ImportOccurrenceResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = ImportOccurrence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ImportOccurrenceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ImportOccurrenceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImportOccurrencesTable::configure($table);
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
            'index' => ListImportOccurrences::route('/'),
            'create' => CreateImportOccurrence::route('/create'),
            'view' => ViewImportOccurrence::route('/{record}'),
            'edit' => EditImportOccurrence::route('/{record}/edit'),
        ];
    }
}
