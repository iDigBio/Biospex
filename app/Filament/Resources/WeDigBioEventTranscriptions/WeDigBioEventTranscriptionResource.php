<?php

namespace App\Filament\Resources\WeDigBioEventTranscriptions;

use App\Filament\Resources\WeDigBioEventTranscriptions\Pages\CreateWeDigBioEventTranscription;
use App\Filament\Resources\WeDigBioEventTranscriptions\Pages\EditWeDigBioEventTranscription;
use App\Filament\Resources\WeDigBioEventTranscriptions\Pages\ListWeDigBioEventTranscriptions;
use App\Filament\Resources\WeDigBioEventTranscriptions\Pages\ViewWeDigBioEventTranscription;
use App\Filament\Resources\WeDigBioEventTranscriptions\Schemas\WeDigBioEventTranscriptionForm;
use App\Filament\Resources\WeDigBioEventTranscriptions\Schemas\WeDigBioEventTranscriptionInfolist;
use App\Filament\Resources\WeDigBioEventTranscriptions\Tables\WeDigBioEventTranscriptionsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\WeDigBioEventTranscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WeDigBioEventTranscriptionResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = WeDigBioEventTranscription::class;

    protected static ?string $modelLabel = 'WeDigBio Event Transcriptions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WeDigBioEventTranscriptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WeDigBioEventTranscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeDigBioEventTranscriptionsTable::configure($table);
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
            'index' => ListWeDigBioEventTranscriptions::route('/'),
            'create' => CreateWeDigBioEventTranscription::route('/create'),
            'view' => ViewWeDigBioEventTranscription::route('/{record}'),
            'edit' => EditWeDigBioEventTranscription::route('/{record}/edit'),
        ];
    }
}
