<?php

namespace App\Filament\Resources\Expeditions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpeditionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('project.title')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('keywords')
                    ->searchable(),
                TextColumn::make('workflow.title')
                    ->searchable(),
                IconColumn::make('completed')
                    ->boolean(),
                IconColumn::make('locked')
                    ->boolean(),
                TextColumn::make('logo_file_name')
                    ->searchable(),
                TextColumn::make('logo_file_size')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('logo_content_type')
                    ->searchable(),
                TextColumn::make('logo_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('logo_path')
                    ->searchable(),
                TextColumn::make('logo_created_at')
                    ->dateTime()
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
