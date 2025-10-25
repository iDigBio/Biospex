<?php

namespace App\Filament\Resources\Expeditions\RelationManagers;

use App\Filament\Resources\Downloads\DownloadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class DownloadsRelationManager extends RelationManager
{
    protected static string $relationship = 'downloads';

    protected static ?string $relatedResource = DownloadResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
