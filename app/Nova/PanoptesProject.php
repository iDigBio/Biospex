<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class PanoptesProject extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var  string
     */
    public static $model = \App\Models\PanoptesProject::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var  string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var  array
     */
    public static $search = [
        'id',
        'project_id',
        'expedition_id',
        'panoptes_project_id',
        'panoptes_workflow_id',
        'title'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return t('Panoptes Projects');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return t('Panoptes Project');
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
            ID::make(t('Id'), 'id')->sortable(),
            BelongsTo::make('Biospex Project', 'project', Project::class)->searchable()->nullable(),
            BelongsTo::make('Biospex Expedition', 'expedition', Expedition::class)->searchable()->nullable(),
            Number::make(t('Panoptes Project Id'), 'panoptes_project_id'),
            Number::make(t('Panoptes Workflow Id'), 'panoptes_workflow_id')->rules('required'),
            Text::make(t('Subject Sets'), 'subject_sets')->onlyOnDetail(),
            Text::make(t('Slug'), 'slug')->onlyOnDetail(),
            Text::make(t('Panoptes Title'), 'title'),
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
