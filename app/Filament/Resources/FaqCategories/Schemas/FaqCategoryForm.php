<?php

namespace App\Filament\Resources\FaqCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FaqCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
