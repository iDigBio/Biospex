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

namespace App\Http\Requests;

use App\Rules\FileUploadNameValidation;
use App\Services\Validation\AssetValidationService;
use Illuminate\Support\Facades\Auth;

/**
 * Class ProjectFormRequest
 */
class ProjectFormRequest extends Request
{
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Validation rules.
     *
     * @return array
     */
    public function rules()
    {
        $projectId = isset($this->route('project')->id) ? $this->route('project')->id : null;
        $assetValidationService = app(AssetValidationService::class);

        $rules = [
            'group_id' => 'required|integer|min:1',
            'title' => 'required|between:6,140|unique:projects,title,'.$projectId,
            'contact' => 'required',
            'contact_email' => 'required|min:4|max:32|email',
            'contact_title' => 'required',
            'description_short' => 'required|between:6,140',
            'description_long' => 'required',
            'keywords' => 'required',
            'organization_website' => 'nullable|url',
            'blog_url' => 'nullable|url',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'logo' => [
                'image',
                new FileUploadNameValidation,
            ],
            'logo_path' => 'nullable|string',
        ];

        // Merge asset validation rules from the shared service
        return array_merge($rules, $assetValidationService->getAssetValidationRules('multiple'));
    }

    /**
     * Alter group form input before validation.
     *
     * @return array
     */
    public function alterInput()
    {
        $input = $this->all();
        $input['title'] = trim($input['title']);
        $this->replace($input);

        return $this->all();
    }

    public function messages()
    {
        return [
            'assets.*.name.required_with' => 'Required when Type selected',
            'assets.*.description.required_with' => 'Required when Type selected',
            'assets.*.download_path.required_if' => 'Required when Type selected',
        ];
    }
}
