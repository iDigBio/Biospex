<?php

namespace App\Filament\Resources\PanoptesProjects;

use App\Filament\Resources\PanoptesProjects\Pages\CreatePanoptesProject;
use App\Filament\Resources\PanoptesProjects\Pages\EditPanoptesProject;
use App\Filament\Resources\PanoptesProjects\Pages\ListPanoptesProjects;
use App\Filament\Resources\PanoptesProjects\Pages\ViewPanoptesProject;
use App\Filament\Resources\PanoptesProjects\Schemas\PanoptesProjectForm;
use App\Filament\Resources\PanoptesProjects\Schemas\PanoptesProjectInfolist;
use App\Filament\Resources\PanoptesProjects\Tables\PanoptesProjectsTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\PanoptesProject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PanoptesProjectResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = PanoptesProject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PanoptesProjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PanoptesProjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PanoptesProjectsTable::configure($table);
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
            'index' => ListPanoptesProjects::route('/'),
            'create' => CreatePanoptesProject::route('/create'),
            'view' => ViewPanoptesProject::route('/{record}'),
            'edit' => EditPanoptesProject::route('/{record}/edit'),
        ];
    }
}
