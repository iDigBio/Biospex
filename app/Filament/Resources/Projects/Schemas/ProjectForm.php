<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Components\ImageFileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
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
                RichEditor::make('description_long')
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
                ImageFileUpload::makeForProject('logo_path')
                    ->label('Project Logo')
                    ->image()
                    ->maxSize(2048)
                    ->imagePreviewHeight('150')
                    ->columnSpanFull(),
                Radio::make('banner_file')
                    ->label('Project Banner')
                    ->options(function () {
                        $bannerOptions = project_banner_options();
                        $formattedOptions = [];

                        foreach ($bannerOptions as $filename => $label) {
                            $imageUrl = project_banner_file_url($filename);
                            $formattedOptions[$filename] = new \Illuminate\Support\HtmlString(
                                '<div class="flex flex-col items-center text-center p-2 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">'.
                                '<img src="'.$imageUrl.'" alt="'.$label.'" class="w-20 h-12 object-cover rounded mb-1" />'.
                                '<span class="text-xs text-gray-700 dark:text-gray-300">'.$label.'</span>'.
                                '</div>'
                            );
                        }

                        return $formattedOptions;
                    })
                    ->default('banner-trees.jpg')
                    ->columns(3)
                    ->columnSpanFull(),
                Textarea::make('target_fields')
                    ->columnSpanFull(),
                TextInput::make('advertise'),
            ]);
    }
}
