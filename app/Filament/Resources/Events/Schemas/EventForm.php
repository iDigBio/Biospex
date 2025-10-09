<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required(),
                Select::make('owner_id')
                    ->label('Owner')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        return User::whereHas('profile', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        })
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn ($user) => [$user->id => $user->getFilamentName()])
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $user = User::find($value);

                        return $user?->getFilamentName();
                    })
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('description')
                    ->required(),
                TextInput::make('hashtag'),
                TextInput::make('contact')
                    ->required(),
                TextInput::make('contact_email')
                    ->email()
                    ->required(),
                DateTimePicker::make('start_date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->required(),
                TextInput::make('timezone')
                    ->required(),
            ]);
    }
}
