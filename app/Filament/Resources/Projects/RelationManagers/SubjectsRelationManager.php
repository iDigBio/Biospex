<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Filament\Resources\Subjects\SubjectResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function ($record) {
                        // Reload the full Subject record to get all attributes
                        $fullRecord = \App\Models\Subject::find($record->id);

                        if (! $fullRecord) {
                            return new HtmlString('<div>Record not found</div>');
                        }

                        $attributes = $fullRecord->toArray();
                        $json = json_encode($attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                        return new HtmlString('<div style="font-family: monospace; white-space: pre; background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; overflow-x: auto;">'.
                            htmlspecialchars($json).
                            '</div>');
                    })
                    ->modalHeading(fn ($record) => 'Subject: '.$record->id)
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                // EditAction::make(),
                // DetachAction::make(),
            ])
            ->headerActions([
                // CreateAction::make(),
                // AttachAction::make(),
            ]);
    }
}
