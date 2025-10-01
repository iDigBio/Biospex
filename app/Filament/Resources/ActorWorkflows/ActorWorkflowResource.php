<?php

namespace App\Filament\Resources\ActorWorkflows;

use App\Filament\Resources\ActorWorkflows\Pages\CreateActorWorkflow;
use App\Filament\Resources\ActorWorkflows\Pages\EditActorWorkflow;
use App\Filament\Resources\ActorWorkflows\Pages\ListActorWorkflows;
use App\Filament\Resources\ActorWorkflows\Pages\ViewActorWorkflow;
use App\Filament\Resources\ActorWorkflows\Schemas\ActorWorkflowForm;
use App\Filament\Resources\ActorWorkflows\Schemas\ActorWorkflowInfolist;
use App\Filament\Resources\ActorWorkflows\Tables\ActorWorkflowsTable;
use App\Models\ActorWorkflow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActorWorkflowResource extends Resource
{
    protected static ?string $model = ActorWorkflow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Actor Workflow';

    public static function form(Schema $schema): Schema
    {
        return ActorWorkflowForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActorWorkflowInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActorWorkflowsTable::configure($table);
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
            'index' => ListActorWorkflows::route('/'),
            'create' => CreateActorWorkflow::route('/create'),
            'view' => ViewActorWorkflow::route('/{record}'),
            'edit' => EditActorWorkflow::route('/{record}/edit'),
        ];
    }
}
