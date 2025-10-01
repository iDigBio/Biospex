<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('first_name'),
                TextEntry::make('last_name'),
                TextEntry::make('timezone'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('avatar_file_name')
                    ->placeholder('-'),
                TextEntry::make('avatar_file_size')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('avatar_content_type')
                    ->placeholder('-'),
                TextEntry::make('avatar_updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('avatar_path')
                    ->placeholder('-'),
                TextEntry::make('avatar_created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
