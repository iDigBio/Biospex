<?php

namespace App\Filament\Resources\TranscriptionLocations;

use App\Filament\Resources\TranscriptionLocations\Pages\CreateTranscriptionLocation;
use App\Filament\Resources\TranscriptionLocations\Pages\EditTranscriptionLocation;
use App\Filament\Resources\TranscriptionLocations\Pages\ListTranscriptionLocations;
use App\Filament\Resources\TranscriptionLocations\Pages\ViewTranscriptionLocation;
use App\Filament\Resources\TranscriptionLocations\Schemas\TranscriptionLocationForm;
use App\Filament\Resources\TranscriptionLocations\Schemas\TranscriptionLocationInfolist;
use App\Filament\Resources\TranscriptionLocations\Tables\TranscriptionLocationsTable;
use App\Models\TranscriptionLocation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TranscriptionLocationResource extends Resource
{
    protected static ?string $model = TranscriptionLocation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TranscriptionLocationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TranscriptionLocationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TranscriptionLocationsTable::configure($table);
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
            'index' => ListTranscriptionLocations::route('/'),
            'create' => CreateTranscriptionLocation::route('/create'),
            'view' => ViewTranscriptionLocation::route('/{record}'),
            'edit' => EditTranscriptionLocation::route('/{record}/edit'),
        ];
    }
}
