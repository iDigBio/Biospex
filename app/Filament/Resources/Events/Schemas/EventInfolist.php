<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('project.title')
                    ->label('Project'),
                TextEntry::make('owner.profile.first_name')
                    ->label('Owner Name')
                    ->formatStateUsing(fn ($record) => $record->owner?->getFilamentName() ?? '-'),
                TextEntry::make('title'),
                TextEntry::make('description'),
                TextEntry::make('hashtag')
                    ->placeholder('-'),
                TextEntry::make('contact'),
                TextEntry::make('contact_email'),
                TextEntry::make('start_date')
                    ->dateTime(),
                TextEntry::make('end_date')
                    ->dateTime(),
                TextEntry::make('timezone'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
