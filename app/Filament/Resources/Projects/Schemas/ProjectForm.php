<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('group_id')
                    ->relationship('group', 'title')
                    ->required(),
                TextInput::make('title'),
                TextInput::make('slug'),
                TextInput::make('contact'),
                TextInput::make('contact_email')
                    ->email(),
                TextInput::make('contact_title'),
                TextInput::make('organization_website')
                    ->url(),
                TextInput::make('organization'),
                Textarea::make('project_partners')
                    ->columnSpanFull(),
                Textarea::make('funding_source')
                    ->columnSpanFull(),
                TextInput::make('description_short'),
                Textarea::make('description_long')
                    ->columnSpanFull(),
                Textarea::make('incentives')
                    ->columnSpanFull(),
                TextInput::make('geographic_scope'),
                TextInput::make('taxonomic_scope'),
                TextInput::make('temporal_scope'),
                TextInput::make('keywords'),
                TextInput::make('blog_url')
                    ->url(),
                TextInput::make('facebook'),
                TextInput::make('twitter'),
                TextInput::make('activities'),
                TextInput::make('language_skills'),
                TextInput::make('logo_file_name'),
                TextInput::make('logo_file_size')
                    ->numeric(),
                TextInput::make('logo_content_type'),
                DateTimePicker::make('logo_updated_at'),
                TextInput::make('logo_path'),
                DateTimePicker::make('logo_created_at'),
                TextInput::make('banner_file'),
                Textarea::make('target_fields')
                    ->columnSpanFull(),
                TextInput::make('advertise'),
            ]);
    }
}
