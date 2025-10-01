<?php

namespace App\Filament\Resources\PusherClassifications;

use App\Filament\Resources\PusherClassifications\Pages\CreatePusherClassification;
use App\Filament\Resources\PusherClassifications\Pages\EditPusherClassification;
use App\Filament\Resources\PusherClassifications\Pages\ListPusherClassifications;
use App\Filament\Resources\PusherClassifications\Pages\ViewPusherClassification;
use App\Filament\Resources\PusherClassifications\Schemas\PusherClassificationForm;
use App\Filament\Resources\PusherClassifications\Schemas\PusherClassificationInfolist;
use App\Filament\Resources\PusherClassifications\Tables\PusherClassificationsTable;
use App\Models\PusherClassification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PusherClassificationResource extends Resource
{
    protected static ?string $model = PusherClassification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PusherClassificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PusherClassificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PusherClassificationsTable::configure($table);
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
            'index' => ListPusherClassifications::route('/'),
            'create' => CreatePusherClassification::route('/create'),
            'view' => ViewPusherClassification::route('/{record}'),
            'edit' => EditPusherClassification::route('/{record}/edit'),
        ];
    }
}
