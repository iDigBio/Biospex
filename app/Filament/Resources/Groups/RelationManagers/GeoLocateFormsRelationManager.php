<?php

namespace App\Filament\Resources\Groups\RelationManagers;

use App\Filament\Resources\GeoLocateForms\GeoLocateFormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class GeoLocateFormsRelationManager extends RelationManager
{
    protected static string $relationship = 'geoLocateForms';

    protected static ?string $relatedResource = GeoLocateFormResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
