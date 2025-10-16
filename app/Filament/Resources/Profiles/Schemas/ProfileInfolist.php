<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(thousandsSeparator: ''),
                TextEntry::make('first_name'),
                TextEntry::make('last_name'),
                TextEntry::make('timezone'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                ImageEntry::make('avatar_display')
                    ->label('Avatar')
                    ->height(150)
                    ->width(150)
                    ->getStateUsing(function ($record) {
                        // Use medium avatar variant for detail views
                        if (! empty($record->avatar_path)) {
                            return $record->present()->showAvatarMedium();
                        }

                        return config('config.missing_avatar_medium');
                    }),
                TextEntry::make('full_name')
                    ->label('Name')
                    ->getStateUsing(function ($record) {
                        return $record->full_name;
                    }),
            ]);
    }
}
