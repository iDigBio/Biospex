<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SubjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('json_document')
                    ->label('MongoDB Document')
                    ->getStateUsing(function ($record) {
                        $attributes = $record->toArray();

                        // Convert ObjectIds and format for display
                        return json_encode((new self)->convertObjectIdsToStrings($attributes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    })
                    ->extraAttributes([
                        'style' => 'white-space: pre; font-family: "JetBrains Mono", "Fira Code", Consolas, monospace; background: #f8f9fa; border: 1px solid #e9ecef; padding: 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; line-height: 1.6; overflow-x: auto;',
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private function convertObjectIdsToStrings(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_object($value) && get_class($value) === 'MongoDB\BSON\ObjectId') {
                $data[$key] = (string) $value;
            } elseif (is_array($value)) {
                $data[$key] = $this->convertObjectIdsToStrings($value);
            }
        }

        return $data;
    }
}
