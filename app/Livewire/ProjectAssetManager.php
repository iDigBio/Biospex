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

namespace App\Livewire;

use Livewire\Component;

class ProjectAssetManager extends Component
{
    public $assets = [];

    public ?string $projectUuid = null;  // Changed from $project to $projectUuid

    public $errors;

    protected $listeners = [
        'fileUploaded' => 'handleFileUploaded',
        'assetUpdated' => '$refresh',
    ];

    public function mount($assets = null, $projectUuid = null, $errors = null)
    {
        $this->projectUuid = $projectUuid;
        $this->errors = $errors;

        // Handle both Collection and array inputs
        if ($assets && (is_array($assets) ? count($assets) > 0 : $assets->isNotEmpty())) {
            // If it's already an array (from controller), use as-is
            // If it's a Collection (from tests or elsewhere), transform it
            if (is_array($assets)) {
                $this->assets = $assets;
            } else {
                $this->assets = $assets->map(function ($asset) {
                    return [
                        'id' => $asset->id ?? null,
                        'type' => $asset->type ?? '',
                        'name' => $asset->name ?? '',
                        'description' => $asset->description ?? '',
                        'download_path' => $asset->download_path ?? '',
                    ];
                })->toArray();
            }
        } else {
            // Initialize as empty array
            $this->assets = [];
            // Start with at least one empty asset
            $this->addAsset();
        }
    }

    public function addAsset()
    {
        // Add a new empty asset array
        $newAsset = [
            'id' => null,
            'type' => '',
            'name' => '',
            'description' => '',
            'download_path' => '',
        ];
        $this->assets[] = $newAsset;
    }

    public function removeAsset($index)
    {
        if (count($this->assets) > 1) {
            unset($this->assets[$index]);
            $this->assets = array_values($this->assets); // Re-index array
        }
    }

    public function handleFileUploaded($eventData)
    {
        // Extract the asset index from the fieldName (e.g., 'download_0' -> 0)
        if (preg_match('/download_(\d+)/', $eventData['fieldName'], $matches)) {
            $index = (int) $matches[1];
            if (isset($this->assets[$index])) {
                $this->assets[$index]['download_path'] = $eventData['filePath'];
                $this->dispatch('assetUpdated');
            }
        }
    }

    public function render()
    {
        return view('livewire.project-asset-manager');
    }
}
