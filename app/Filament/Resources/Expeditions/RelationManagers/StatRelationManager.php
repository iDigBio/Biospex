<?php

namespace App\Filament\Resources\Expeditions\RelationManagers;

use App\Filament\Resources\ExpeditionStats\ExpeditionStatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class StatRelationManager extends RelationManager
{
    protected static string $relationship = 'stat';

    protected static ?string $relatedResource = ExpeditionStatResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
