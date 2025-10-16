<?php

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exported')
                    ->label('Exported')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->sortable(),
                TextColumn::make('json_preview')
                    ->label('Document Preview')
                    ->searchable(query: function ($query, $search) {
                        return $query->where(function ($query) use ($search) {
                            // Get all the model's fillable attributes or all columns
                            $model = new \App\Models\Subject; // Replace with your actual model
                            $searchableColumns = $model->getFillable();

                            foreach ($searchableColumns as $column) {
                                $query->orWhere($column, 'like', "%{$search}%");
                            }
                        });
                    })
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
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //    DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
