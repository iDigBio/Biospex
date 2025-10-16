<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Filament\Resources\Bingos\BingoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BingosRelationManager extends RelationManager
{
    protected static string $relationship = 'bingos';

    protected static ?string $relatedResource = BingoResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
