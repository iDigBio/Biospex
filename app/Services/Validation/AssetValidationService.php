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

namespace App\Services\Validation;

use App\Rules\AssetDownloadPathValidation;
use App\Rules\AssetDownloadValidation;
use App\Rules\AssetNameValidation;

class AssetValidationService
{
    /**
     * Get asset validation rules based on context
     *
     * @param  string  $context  'multiple' for Project forms, 'single' for Filament forms
     */
    public function getAssetValidationRules(string $context = 'multiple'): array
    {
        if ($context === 'single') {
            return [
                'type' => 'required|in:'.implode(',', config('config.project_assets', [])),
                'name' => ['required', function ($attribute, $value, $fail) {
                    $type = request('type');
                    if (in_array($type, ['Website URL', 'Video URL'])) {
                        if (! filter_var($value, FILTER_VALIDATE_URL)) {
                            $fail('Must be valid URL');
                        }
                    }
                }],
                'description' => 'required',
                'download_path' => [function ($attribute, $value, $fail) {
                    $type = request('type');
                    if ($type === 'File Download') {
                        if (empty($value) && ! request()->hasFile('download_file')) {
                            $fail('File required for File Download type');
                        }
                        if (request()->hasFile('download_file')) {
                            $file = request()->file('download_file');
                            $allowedMimes = ['txt', 'doc', 'csv', 'pdf'];
                            if (! in_array($file->getClientOriginalExtension(), $allowedMimes)) {
                                $fail('Accepted files: txt,doc,csv,pdf');
                            }
                            if (! preg_match('/^[\w\-.]+$/', $file->getClientOriginalName())) {
                                $fail('File name can only contain alphanumeric, dash, or underscore.');
                            }
                        }
                    }
                }],
            ];
        }

        // Multiple assets context (existing ProjectFormRequest format)
        return [
            'assets.*.type' => [new AssetDownloadValidation],
            'assets.*.name' => ['required_with:assets.*.type', new AssetNameValidation],
            'assets.*.description' => 'required_with:assets.*.type',
            'assets.*.download_path' => [new AssetDownloadPathValidation],
        ];
    }

    /**
     * Validate individual asset type and content
     *
     * @return array Array of validation errors (empty if valid)
     */
    public function validateAssetType(string $type, ?string $name = null, ?string $downloadPath = null): array
    {
        $errors = [];

        if (! in_array($type, config('config.project_assets', []))) {
            $errors[] = 'Invalid asset type';

            return $errors;
        }

        // Validate based on type requirements
        $requirements = $this->getAssetTypeRequirements($type);

        foreach ($requirements as $field => $rule) {
            switch ($field) {
                case 'name':
                    if ($rule['required'] && empty($name)) {
                        $errors[] = 'Name is required';
                    } elseif ($rule['type'] === 'url' && ! empty($name) && ! filter_var($name, FILTER_VALIDATE_URL)) {
                        $errors[] = 'Must be valid URL';
                    }
                    break;

                case 'download_path':
                    if ($rule['required'] && empty($downloadPath)) {
                        $errors[] = 'File required for File Download type';
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Get validation messages based on context
     */
    public function getValidationMessages(string $context = 'multiple'): array
    {
        if ($context === 'single') {
            return [
                'type.required' => 'Asset type is required',
                'type.in' => 'Invalid asset type',
                'name.required' => 'Name is required',
                'description.required' => 'Description is required',
            ];
        }

        // Multiple assets context (existing ProjectFormRequest messages)
        return [
            'assets.*.name.required_with' => 'Required when Type selected',
            'assets.*.description.required_with' => 'Required when Type selected',
            'assets.*.download_path.required_if' => 'Required when Type selected',
        ];
    }

    /**
     * Get asset type requirements
     */
    public function getAssetTypeRequirements(string $type): array
    {
        switch ($type) {
            case 'Website URL':
            case 'Video URL':
                return [
                    'name' => ['required' => true, 'type' => 'url'],
                    'description' => ['required' => true],
                    'download_path' => ['required' => false],
                ];

            case 'File Download':
                return [
                    'name' => ['required' => true, 'type' => 'text'],
                    'description' => ['required' => true],
                    'download_path' => ['required' => true],
                ];

            default:
                return [
                    'name' => ['required' => true, 'type' => 'text'],
                    'description' => ['required' => true],
                    'download_path' => ['required' => false],
                ];
        }
    }

    /**
     * Get reactive validation rules for Filament forms based on selected type
     */
    public function getReactiveValidationRules(string $selectedType): array
    {
        $requirements = $this->getAssetTypeRequirements($selectedType);
        $rules = [];

        foreach ($requirements as $field => $rule) {
            if ($rule['required']) {
                $rules[$field] = 'required';

                if ($field === 'name' && $rule['type'] === 'url') {
                    $rules[$field] = 'required|url';
                }
            } else {
                $rules[$field] = 'nullable';
            }
        }

        return $rules;
    }

    /**
     * Check if field should be visible based on asset type
     */
    public function shouldFieldBeVisible(string $field, string $type): bool
    {
        if ($field === 'download_path') {
            return $type === 'File Download';
        }

        // Other fields are always visible
        return true;
    }

    /**
     * Get field label based on asset type
     */
    public function getFieldLabel(string $field, string $type): string
    {
        if ($field === 'name') {
            if (in_array($type, ['Website URL', 'Video URL'])) {
                return 'URL';
            }

            return 'Asset Name';
        }

        return ucfirst(str_replace('_', ' ', $field));
    }
}
