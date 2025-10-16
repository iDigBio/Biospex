<?php

namespace App\Filament\Resources\WeDigBioEvents;

use App\Filament\Resources\WeDigBioEvents\Pages\CreateWeDigBioEvent;
use App\Filament\Resources\WeDigBioEvents\Pages\EditWeDigBioEvent;
use App\Filament\Resources\WeDigBioEvents\Pages\ListWeDigBioEvents;
use App\Filament\Resources\WeDigBioEvents\Pages\ViewWeDigBioEvent;
use App\Filament\Resources\WeDigBioEvents\Schemas\WeDigBioEventForm;
use App\Filament\Resources\WeDigBioEvents\Schemas\WeDigBioEventInfolist;
use App\Filament\Resources\WeDigBioEvents\Tables\WeDigBioEventsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\WeDigBioEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WeDigBioEventResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = WeDigBioEvent::class;

    protected static ?string $modelLabel = 'WeDigBio Events';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WeDigBioEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WeDigBioEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeDigBioEventsTable::configure($table);
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
            'index' => ListWeDigBioEvents::route('/'),
            'create' => CreateWeDigBioEvent::route('/create'),
            'view' => ViewWeDigBioEvent::route('/{record}'),
            'edit' => EditWeDigBioEvent::route('/{record}/edit'),
        ];
    }
}
