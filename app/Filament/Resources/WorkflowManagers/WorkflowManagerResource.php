<?php

namespace App\Filament\Resources\WorkflowManagers;

use App\Filament\Resources\WorkflowManagers\Pages\CreateWorkflowManager;
use App\Filament\Resources\WorkflowManagers\Pages\EditWorkflowManager;
use App\Filament\Resources\WorkflowManagers\Pages\ListWorkflowManagers;
use App\Filament\Resources\WorkflowManagers\Pages\ViewWorkflowManager;
use App\Filament\Resources\WorkflowManagers\Schemas\WorkflowManagerForm;
use App\Filament\Resources\WorkflowManagers\Schemas\WorkflowManagerInfolist;
use App\Filament\Resources\WorkflowManagers\Tables\WorkflowManagersTable;
use App\Filament\Traits\NavigationTrait;
use App\Models\WorkflowManager;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkflowManagerResource extends Resource
{
    use NavigationTrait;

    protected static ?string $model = WorkflowManager::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WorkflowManagerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WorkflowManagerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkflowManagersTable::configure($table);
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
            'index' => ListWorkflowManagers::route('/'),
            'create' => CreateWorkflowManager::route('/create'),
            'view' => ViewWorkflowManager::route('/{record}'),
            'edit' => EditWorkflowManager::route('/{record}/edit'),
        ];
    }
}
