<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\DateTime;

class Project extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var  string
     */
    public static $model = \App\Models\Project::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var  string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var  array
     */
    public static $search = [
        'id',
        'group_id',
        'title',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return t('Projects');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return t('Project');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return  array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(t('Id'), 'id')->onlyOnDetail()->sortable(),
            Text::make(t('Uuid'), 'uuid')->onlyOnDetail(),
            BelongsTo::make('Group')->rules('required')->sortable(),
            Select::make(t('Status'), 'status')->rules('required')->hideFromIndex()->options([
                'starting' => 'Starting',
                'active'   => 'Active',
                'complete' => 'Complete',
                'hiatus'   => 'Hiatus',
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
     * @param \Illuminate\Http\Request $request
     * @return  array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return  array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return  array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return  array
     */
    public function actions(Request $request)
    {
        return [];
    }
}