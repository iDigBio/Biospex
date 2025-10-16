<?php

namespace App\Filament\Resources\WeDigBioProjects;

use App\Filament\Resources\WeDigBioProjects\Pages\CreateWeDigBioProject;
use App\Filament\Resources\WeDigBioProjects\Pages\EditWeDigBioProject;
use App\Filament\Resources\WeDigBioProjects\Pages\ListWeDigBioProjects;
use App\Filament\Resources\WeDigBioProjects\Pages\ViewWeDigBioProject;
use App\Filament\Resources\WeDigBioProjects\Schemas\WeDigBioProjectForm;
use App\Filament\Resources\WeDigBioProjects\Schemas\WeDigBioProjectInfolist;
use App\Filament\Resources\WeDigBioProjects\Tables\WeDigBioProjectsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\WeDigBioProject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WeDigBioProjectResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = WeDigBioProject::class;

    protected static ?string $modelLabel = 'WeDigBio Projects';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WeDigBioProjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WeDigBioProjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeDigBioProjectsTable::configure($table);
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
            'index' => ListWeDigBioProjects::route('/'),
            'create' => CreateWeDigBioProject::route('/create'),
            'view' => ViewWeDigBioProject::route('/{record}'),
            'edit' => EditWeDigBioProject::route('/{record}/edit'),
        ];
    }
}
