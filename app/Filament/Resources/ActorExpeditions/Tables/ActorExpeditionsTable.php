<?php

namespace App\Filament\Resources\ActorExpeditions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActorExpeditionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expedition.title')
                    ->searchable(),
                TextColumn::make('actor.title')
                    ->searchable(),
                TextColumn::make('state')
                    ->numeric(thousandsSeparator: '')
                    ->sortable(),
                TextColumn::make('total')
                    ->numeric(thousandsSeparator: '')
                    ->sortable(),
                TextColumn::make('error')
                    ->numeric(thousandsSeparator: '')
                    ->sortable(),
                TextColumn::make('order')
                    ->numeric(thousandsSeparator: '')
                    ->sortable(),
                TextColumn::make('expert')
                    ->numeric(thousandsSeparator: '')
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
