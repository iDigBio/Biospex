<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Filament\Resources\Subjects\SubjectResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';

    protected static ?string $relatedResource = SubjectResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('expeditions_list')
                    ->label('Expeditions')
                    ->getStateUsing(function ($record) {
                        return $record->expeditions->pluck('title')->join(', ') ?: '-';
                    })
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('exported')
                    ->label('Exported')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->sortable(),
                TextColumn::make('json_preview')
                    ->label('Document Preview')
                    ->getStateUsing(function ($record) {
                        $attributes = $record->toArray();
                        $preview = json_encode($attributes, JSON_UNESCAPED_UNICODE);

                        return strlen($preview) > 50 ? substr($preview, 0, 50).'...' : $preview;
                    })
                    ->extraAttributes(['style' => 'font-family: monospace; font-size: 0.75rem;'])
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
