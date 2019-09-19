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
        return __('Projects');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Project');
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
            ID::make(__('Id'), 'id')->onlyOnDetail()->sortable(),
            Text::make(__('Uuid'), 'uuid')->onlyOnDetail(),
            BelongsTo::make('Group')->rules('required')->sortable(),
            Select::make(__('Status'), 'status')->rules('required')->hideFromIndex()->options([
                'starting' => 'Starting',
                'active'   => 'Active',
                'complete' => 'Complete',
                'hiatus'   => 'Hiatus',
            ]),
            Text::make(__('Title'), 'title')->rules('required')->sortable(),
            Text::make(__('Slug'), 'slug')->onlyOnDetail(),
            Text::make(__('Contact'), 'contact')->rules('required')->hideFromIndex(),
            Text::make(__('Contact Email'), 'contact_email')->rules('required')->hideFromIndex(),
            Text::make(__('Contact Title'), 'contact_title')->rules('required')->hideFromIndex(),
            Text::make(__('Organization Website'), 'organization_website')->onlyOnDetail(),
            Text::make(__('Organization'), 'organization')->onlyOnDetail(),
            Textarea::make(__('Project Partners'), 'project_partners')->onlyOnDetail(),
            Textarea::make(__('Funding Source'), 'funding_source')->onlyOnDetail(),
            Text::make(__('Description Short'), 'description_short')->rules('required')->hideFromIndex(),
            Textarea::make(__('Description Long'), 'description_long')->rules('required')->hideFromIndex(),
            Textarea::make(__('Incentives'), 'incentives')->onlyOnDetail(),
            Text::make(__('Geographic Scope'), 'geographic_scope')->onlyOnDetail(),
            Text::make(__('Taxonomic Scope'), 'taxonomic_scope')->onlyOnDetail(),
            Text::make(__('Temporal Scope'), 'temporal_scope')->onlyOnDetail(),
            Text::make(__('Keywords'), 'keywords')->rules('required')->hideFromIndex(),
            Text::make(__('Blog Url'), 'blog_url')->onlyOnDetail(),
            Text::make(__('Facebook'), 'facebook')->onlyOnDetail(),
            Text::make(__('Twitter'), 'twitter')->onlyOnDetail(),
            Text::make(__('Activities'), 'activities')->onlyOnDetail(),
            Text::make(__('Language Skills'), 'language_skills')->onlyOnDetail(),
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
