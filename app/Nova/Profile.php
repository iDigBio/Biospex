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
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;
use Laravel\Nova\Http\Requests\NovaRequest;

use function t;

/**
 * Class Profile
 */
class Profile extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Profile::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'first_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'first_name',
        'last_name',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return t('Profiles');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return t('Profile');
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
            BelongsTo::make('User')->rules('required')->searchable()->sortable(),
            Text::make(t('First Name'), 'first_name')->rules('required')->sortable(),
            Text::make(t('Last Name'), 'last_name')->rules('required')->sortable(),
            Timezone::make(t('Timezone'), 'timezone')->rules('required')->onlyOnForms(),
            Image::make(t('Avatar File Name'), 'avatar')
                ->store(function (NovaRequest $request, $model) {
                    return [
                        'avatar' => $request->avatar,
                        'avatar_file_name' => $request->avatar->getClientOriginalName(),
                        'avatar_file_size' => $request->avatar->getSize(),
                        'avatar_content_type' => $request->avatar->getMimeType(),
                    ];
                })->deletable()->prunable()->resolveUsing(function () {
                    $baseLength = strlen(config('paperclip.storage.base-urls.public'));
                    $relativePath = substr($this->avatar->url('medium'), $baseLength);

                    return $relativePath;
                }),
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
