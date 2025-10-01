<?php

namespace App\Filament\Resources\Expeditions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExpeditionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('project.title')
                    ->label('Project'),
                TextEntry::make('title'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('keywords'),
                TextEntry::make('workflow.title')
                    ->label('Workflow')
                    ->placeholder('-'),
                IconEntry::make('completed')
                    ->boolean(),
                IconEntry::make('locked')
                    ->boolean(),
                TextEntry::make('logo_file_name')
                    ->placeholder('-'),
                TextEntry::make('logo_file_size')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('logo_content_type')
                    ->placeholder('-'),
                TextEntry::make('logo_updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('logo_path')
                    ->placeholder('-'),
                TextEntry::make('logo_created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
