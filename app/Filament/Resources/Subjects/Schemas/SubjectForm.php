<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('json_editor')
                    ->label('MongoDB Document (JSON)')
                    ->helperText('Edit the complete MongoDB document. Changes will be saved directly to the database.')
                    ->rows(25)
                    ->extraAttributes([
                        'style' => 'font-family: "JetBrains Mono", "Fira Code", Consolas, monospace; font-size: 0.875rem; line-height: 1.5;',
                        'spellcheck' => 'false',
                    ])
                    ->afterStateHydrated(function ($component, $record) {
                        if (! $record) {
                            return;
                        }

                        $attributes = $record->toArray();
                        $component->state(json_encode($attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    })
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }
}
