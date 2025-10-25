<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8),
                        Toggle::make('notification')
                            ->label('Email Notifications')
                            ->default(true),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Profile Information')
                    ->relationship('profile')
                    ->schema([
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Select::make('timezone')
                            ->options([
                                'America/New_York' => 'Eastern Time',
                                'America/Chicago' => 'Central Time',
                                'America/Denver' => 'Mountain Time',
                                'America/Los_Angeles' => 'Pacific Time',
                                'UTC' => 'UTC',
                            ])
                            ->default('UTC')
                            ->required(),
                        FileUpload::make('avatar')
                            ->image()
                            ->directory('avatars')
                            ->visibility('public'),
                    ])
                    ->columns(2),
            ]);
    }
}
