<?php

namespace App\Filament\Resources\Groups\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Group Title'),
                Select::make('user_id')
                    ->label('Group Owner')
                    ->relationship('owner', 'id')
                    ->getOptionLabelFromRecordUsing(fn (User $record) => $record->getFilamentName())
                    ->searchable(['profile.first_name', 'profile.last_name'])
                    ->required()
                    ->preload(),
            ]);
    }
}
