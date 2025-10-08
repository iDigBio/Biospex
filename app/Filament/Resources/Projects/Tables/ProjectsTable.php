<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group.title')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('contact')
                    ->searchable(),
                TextColumn::make('contact_email')
                    ->searchable(),
                TextColumn::make('contact_title')
                    ->searchable(),
                TextColumn::make('organization_website')
                    ->searchable(),
                TextColumn::make('organization')
                    ->searchable(),
                TextColumn::make('description_short')
                    ->searchable(),
                TextColumn::make('geographic_scope')
                    ->searchable(),
                TextColumn::make('taxonomic_scope')
                    ->searchable(),
                TextColumn::make('temporal_scope')
                    ->searchable(),
                TextColumn::make('keywords')
                    ->searchable(),
                TextColumn::make('blog_url')
                    ->searchable(),
                TextColumn::make('facebook')
                    ->searchable(),
                TextColumn::make('twitter')
                    ->searchable(),
                TextColumn::make('activities')
                    ->searchable(),
                TextColumn::make('language_skills')
                    ->searchable(),
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
                TextColumn::make('banner_file')
                    ->searchable(),
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
