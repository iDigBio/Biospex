<?php

namespace App\Filament\Resources\TeamCategories;

use App\Filament\Resources\TeamCategories\Pages\CreateTeamCategory;
use App\Filament\Resources\TeamCategories\Pages\EditTeamCategory;
use App\Filament\Resources\TeamCategories\Pages\ListTeamCategories;
use App\Filament\Resources\TeamCategories\Pages\ViewTeamCategory;
use App\Filament\Resources\TeamCategories\Schemas\TeamCategoryForm;
use App\Filament\Resources\TeamCategories\Schemas\TeamCategoryInfolist;
use App\Filament\Resources\TeamCategories\Tables\TeamCategoriesTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\TeamCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TeamCategoryResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = TeamCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TeamCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeamCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamCategoriesTable::configure($table);
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
            'index' => ListTeamCategories::route('/'),
            'create' => CreateTeamCategory::route('/create'),
            'view' => ViewTeamCategory::route('/{record}'),
            'edit' => EditTeamCategory::route('/{record}/edit'),
        ];
    }
}
