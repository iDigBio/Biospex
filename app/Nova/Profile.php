<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;

class Profile extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var  string
     */
    public static $model = \App\Models\Profile::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var  string
     */
    public static $title = 'first_name';

    /**
     * The columns that should be searched.
     *
     * @var  array
     */
    public static $search = [
        'id',
        'first_name',
        'last_name',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Profiles');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Profile');
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
            ID::make(__('Id'), 'id')->sortable(),
            BelongsTo::make('User')->rules('required')->searchable()->sortable(),
            Text::make(__('First Name'), 'first_name')->rules('required')->sortable(),
            Text::make(__('Last Name'), 'last_name')->rules('required')->sortable(),
            Timezone::make(__('Timezone'), 'timezone')->rules('required')->onlyOnForms(),
            Image::make(__('Avatar File Name'), 'avatar')
                ->store(function (Request $request, $model) {
                    return [
                        'avatar' => $request->avatar,
                        'avatar_file_name' => $request->avatar->getClientOriginalName(),
                        'avatar_file_size' => $request->avatar->getSize(),
                        'avatar_content_type' => $request->avatar->getMimeType()
                    ];
                })->deletable()->prunable()->resolveUsing(function () {
                    $baseLength   = strlen(config('paperclip.storage.base-urls.public'));
                    $relativePath = substr($this->avatar->url('medium'), $baseLength);
                    return $relativePath;
                }),
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
