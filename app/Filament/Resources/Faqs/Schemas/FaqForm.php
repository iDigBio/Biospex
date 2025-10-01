<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('faq_category_id')
                    ->relationship('faqCategory', 'name')
                    ->required(),
                TextInput::make('question')
                    ->required(),
                TextInput::make('answer')
                    ->required(),
            ]);
    }
}
