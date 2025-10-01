<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('group.title')
                    ->label('Group'),
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('slug')
                    ->placeholder('-'),
                TextEntry::make('contact')
                    ->placeholder('-'),
                TextEntry::make('contact_email')
                    ->placeholder('-'),
                TextEntry::make('contact_title')
                    ->placeholder('-'),
                TextEntry::make('organization_website')
                    ->placeholder('-'),
                TextEntry::make('organization')
                    ->placeholder('-'),
                TextEntry::make('project_partners')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('funding_source')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('description_short')
                    ->placeholder('-'),
                TextEntry::make('description_long')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('incentives')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('geographic_scope')
                    ->placeholder('-'),
                TextEntry::make('taxonomic_scope')
                    ->placeholder('-'),
                TextEntry::make('temporal_scope')
                    ->placeholder('-'),
                TextEntry::make('keywords')
                    ->placeholder('-'),
                TextEntry::make('blog_url')
                    ->placeholder('-'),
                TextEntry::make('facebook')
                    ->placeholder('-'),
                TextEntry::make('twitter')
                    ->placeholder('-'),
                TextEntry::make('activities')
                    ->placeholder('-'),
                TextEntry::make('language_skills')
                    ->placeholder('-'),
                TextEntry::make('logo_file_name')
                    ->placeholder('-'),
                TextEntry::make('logo_file_size')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('logo_content_type')
                    ->placeholder('-'),
                TextEntry::make('logo_updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('logo_path')
                    ->placeholder('-'),
                TextEntry::make('logo_created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('banner_file')
                    ->placeholder('-'),
                TextEntry::make('target_fields')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
