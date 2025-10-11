<?php

namespace App\Filament\Resources\Profiles\Schemas;

use App\Filament\Components\ImageFileUpload;
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
                ImageFileUpload::makeForProfile('avatar_path')
                    ->label('Avatar')
                    ->image()
                    ->maxSize(2048)
                    ->imagePreviewHeight('150')
                    ->helperText('Upload a profile avatar (JPEG, PNG, GIF). Maximum 2MB.'),
            ]);
    }
}
