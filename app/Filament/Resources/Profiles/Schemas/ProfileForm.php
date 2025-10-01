<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('timezone')
                    ->required(),
                TextInput::make('avatar_file_name'),
                TextInput::make('avatar_file_size')
                    ->numeric(),
                TextInput::make('avatar_content_type'),
                DateTimePicker::make('avatar_updated_at'),
                TextInput::make('avatar_path'),
                DateTimePicker::make('avatar_created_at'),
            ]);
    }
}
