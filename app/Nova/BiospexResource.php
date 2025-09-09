<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class BiospexResource
 */
class BiospexResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Resource';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title',
        'description',
        'download_path',
        'order',
    ];

    public static function label()
    {
        return 'Resources';
    }

    public static function singularLabel()
    {
        return 'Resource';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Title'),
            Textarea::make('Description'),
            File::make('Document')
                ->disk('s3')
                ->path('uploads/resources')
                ->store(function (NovaRequest $request, $model) {
                    $filename = time().'_'.$request->document->getClientOriginalName();
                    $path = 'uploads/resources/'.$filename;

                    // Store the file on S3
                    $request->document->storeAs('uploads/resources', $filename, 's3');

                    return [
                        'download_path' => $path,
                        'document_file_name' => $request->document->getClientOriginalName(),
                        'document_file_size' => $request->document->getSize(),
                        'document_content_type' => $request->document->getMimeType(),
                    ];
                })
                ->prunable()
                ->resolveUsing(function ($download_path, $resource) {
                    if ($download_path) {
                        // Check if file exists on S3 or local storage
                        $filename = basename($download_path);

                        // Check S3 first
                        if (Storage::disk('s3')->exists($download_path)) {
                            return $filename;
                        }
                        // Check local storage as fallback
                        elseif (Storage::disk('local')->exists($download_path)) {
                            return $filename;
                        }

                        // File exists in database but not in storage
                        return $filename;
                    }

                    // Check for old paperclip files if no download_path but document_file_name exists
                    if (isset($resource->document_file_name) && $resource->document_file_name) {
                        return basename($resource->document_file_name);
                    }

                    return null;
                })
                ->displayUsing(function ($value, $resource, $attribute) {
                    if ($resource->download_path) {
                        $filename = basename($resource->download_path);
                        $status = '';

                        // Check S3 first
                        if (Storage::disk('s3')->exists($resource->download_path)) {
                            $status = ' ✓ (S3)';
                        }
                        // Check local storage as fallback
                        elseif (Storage::disk('local')->exists($resource->download_path)) {
                            $status = ' ✓ (Local)';
                        } else {
                            $status = ' ⚠ (Missing)';
                        }

                        return $filename.$status;
                    }

                    // Check for old paperclip files if no download_path but document_file_name exists
                    if (isset($resource->document_file_name) && $resource->document_file_name) {
                        $filename = basename($resource->document_file_name);
                        $idPartition = sprintf('%03d/%03d/%03d', 0, 0, $resource->id);
                        $oldPath = "paperclip/App/Models/Resource/documents/{$idPartition}/original/{$resource->document_file_name}";
                        $status = '';

                        // Check if old paperclip file exists in public storage
                        if (Storage::disk('public')->exists($oldPath)) {
                            $status = ' ✓ (Paperclip)';
                        } else {
                            $status = ' ⚠ (Missing)';
                        }

                        return $filename.$status;
                    }

                    return 'No file uploaded';
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
