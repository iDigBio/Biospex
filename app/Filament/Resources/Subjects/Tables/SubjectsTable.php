<?php

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                        // Use MongoDB's native text search (requires text index)
                        return $query->whereRaw([
                            '$text' => [
                                '$search' => $search,
                                '$caseSensitive' => false,
                            ],
                        ]);
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
            ->recordUrl(null) // This disables the default row click navigation
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->preload(),
                Filter::make('occurrence.institutionCode')
                    ->label('Institution Code')
                    ->schema([
                        TextInput::make('institutionCode')
                            ->label('Institution Code'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['institutionCode'],
                            fn ($query, $value) => $query->where('occurrence.institutionCode', 'like', '%'.$value.'%')
                        );
                    }),

                Filter::make('occurrence.genus')
                    ->label('Genus')
                    ->schema([
                        TextInput::make('genus')
                            ->label('Genus'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['genus'],
                            fn ($query, $value) => $query->where('occurrence.genus', 'like', '%'.$value.'%')
                        );
                    }),

                Filter::make('occurrence.scientificName')
                    ->label('Scientific Name')
                    ->schema([
                        TextInput::make('scientificName')
                            ->label('Scientific Name'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['scientificName'],
                            fn ($query, $value) => $query->where('occurrence.scientificName', 'like', '%'.$value.'%')
                        );
                    }),

                Filter::make('occurrence.stateProvince')
                    ->label('State/Province')
                    ->schema([
                        TextInput::make('stateProvince')
                            ->label('State/Province'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['stateProvince'],
                            fn ($query, $value) => $query->where('occurrence.stateProvince', 'like', '%'.$value.'%')
                        );
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function ($record) {
                        $attributes = $record->toArray();
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
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //    DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
