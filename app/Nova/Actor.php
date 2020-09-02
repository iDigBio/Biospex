<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;

class Actor extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var  string
     */
    public static $model = \App\Models\Actor::class;

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
        'title',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return t('Actors');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return t('Actor');
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
            Text::make(t('Title'), 'title')->rules('required')->sortable(),
            Text::make(t('Url'), 'url')->rules('required')->sortable(),
            Text::make(t('Class'), 'class')->rules('required')->sortable(),
            Boolean::make(t('Private'), 'private')
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
