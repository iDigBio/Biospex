<?php

/*
 * Copyright (c) 2022. Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

use function t;

/**
 * Class Project
 */
class Project extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Project::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'group_id',
        'title',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return t('Projects');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return t('Project');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make(t('Id'), 'id')->sortable(),
            Text::make(t('Uuid'), 'uuid')->onlyOnDetail(),
            BelongsTo::make('Group')->rules('required')->sortable(),
            Select::make(t('Status'), 'status')->rules('required')->hideFromIndex()->options([
                'starting' => 'Starting',
                'active' => 'Active',
                'complete' => 'Complete',
                'hiatus' => 'Hiatus',
            ]),
            Text::make(t('Title'), 'title')->rules('required')->sortable(),
            Text::make(t('Slug'), 'slug')->onlyOnDetail(),
            Text::make(t('Contact'), 'contact')->rules('required')->hideFromIndex(),
            Text::make(t('Contact Email'), 'contact_email')->rules('required')->hideFromIndex(),
            Text::make(t('Contact Title'), 'contact_title')->rules('required')->hideFromIndex(),
            Text::make(t('Organization Website'), 'organization_website')->onlyOnDetail(),
            Text::make(t('Organization'), 'organization')->onlyOnDetail(),
            Textarea::make(t('Project Partners'), 'project_partners')->onlyOnDetail(),
            Textarea::make(t('Funding Source'), 'funding_source')->onlyOnDetail(),
            Text::make(t('Description Short'), 'description_short')->rules('required')->hideFromIndex(),
            Textarea::make(t('Description Long'), 'description_long')->rules('required')->hideFromIndex(),
            Textarea::make(t('Incentives'), 'incentives')->onlyOnDetail(),
            Text::make(t('Geographic Scope'), 'geographic_scope')->onlyOnDetail(),
            Text::make(t('Taxonomic Scope'), 'taxonomic_scope')->onlyOnDetail(),
            Text::make(t('Temporal Scope'), 'temporal_scope')->onlyOnDetail(),
            Text::make(t('Keywords'), 'keywords')->rules('required')->hideFromIndex(),
            Text::make(t('Blog Url'), 'blog_url')->onlyOnDetail(),
            Text::make(t('Facebook'), 'facebook')->onlyOnDetail(),
            Text::make(t('Twitter'), 'twitter')->onlyOnDetail(),
            Text::make(t('Activities'), 'activities')->onlyOnDetail(),
            Text::make(t('Language Skills'), 'language_skills')->onlyOnDetail(),
            HasOne::make('Workflow')->rules('required')->hideFromIndex(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
