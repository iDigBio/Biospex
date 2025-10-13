<?php

namespace App\Filament\Resources\Groups\RelationManagers;

use App\Filament\Resources\Expeditions\ExpeditionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ExpeditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'expeditions';

    protected static ?string $relatedResource = ExpeditionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
