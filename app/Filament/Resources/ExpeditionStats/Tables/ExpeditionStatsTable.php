<?php

namespace App\Filament\Resources\ExpeditionStats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpeditionStatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expedition.title')
                    ->searchable(),
                TextColumn::make('local_subject_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subject_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('transcriptions_goal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('local_transcriptions_completed')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('transcriptions_completed')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('transcriber_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('percent_completed')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
