<?php

namespace App\Filament\Resources\Bingos\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BingoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
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

                Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('directions')
                    ->required(),
                TextInput::make('contact')
                    ->required(),
            ]);
    }
}
