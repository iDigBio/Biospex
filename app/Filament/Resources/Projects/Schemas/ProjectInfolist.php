<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                ImageEntry::make('logo_path')
                    ->label('Project Logo')
                    ->disk('s3')
                    ->height(150)
                    ->placeholder(config('config.missing_project_logo'))
                    ->columnSpanFull(),
                ImageEntry::make('banner_file')
                    ->label('Project Banner')
                    ->disk('public')
                    ->getStateUsing(function ($record): ?string {
                        return $record->banner_file ? 'images/habitat-banners/'.$record->banner_file : 'images/habitat-banners/banner-trees.jpg';
                    })
                    ->height(50)
                    ->defaultImageUrl('/images/habitat-banners/banner-trees.jpg')
                    ->columnSpanFull(),
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
