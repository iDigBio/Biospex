<?php

namespace App\Filament\Resources\EventTranscriptions;

use App\Filament\Resources\EventTranscriptions\Pages\CreateEventTranscription;
use App\Filament\Resources\EventTranscriptions\Pages\EditEventTranscription;
use App\Filament\Resources\EventTranscriptions\Pages\ListEventTranscriptions;
use App\Filament\Resources\EventTranscriptions\Pages\ViewEventTranscription;
use App\Filament\Resources\EventTranscriptions\Schemas\EventTranscriptionForm;
use App\Filament\Resources\EventTranscriptions\Schemas\EventTranscriptionInfolist;
use App\Filament\Resources\EventTranscriptions\Tables\EventTranscriptionsTable;
use App\Models\EventTranscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventTranscriptionResource extends Resource
{
    protected static ?string $model = EventTranscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EventTranscriptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EventTranscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventTranscriptionsTable::configure($table);
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
            'index' => ListEventTranscriptions::route('/'),
            'create' => CreateEventTranscription::route('/create'),
            'view' => ViewEventTranscription::route('/{record}'),
            'edit' => EditEventTranscription::route('/{record}/edit'),
        ];
    }
}
