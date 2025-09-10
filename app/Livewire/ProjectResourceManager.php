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

class ProjectResourceManager extends Component
{
    public $resources = [];

    public ?string $projectUuid = null;  // Changed from $project to $projectUuid

    public $errors;

    protected $listeners = [
        'fileUploaded' => 'handleFileUploaded',
        'resourceUpdated' => '$refresh',
    ];

    public function mount($resources = null, $projectUuid = null, $errors = null)
    {
        $this->projectUuid = $projectUuid;
        $this->errors = $errors;

        // Handle both Collection and array inputs
        if ($resources && (is_array($resources) ? count($resources) > 0 : $resources->isNotEmpty())) {
            // If it's already an array (from controller), use as-is
            // If it's a Collection (from tests or elsewhere), transform it
            if (is_array($resources)) {
                $this->resources = $resources;
            } else {
                $this->resources = $resources->map(function ($resource) {
                    return [
                        'id' => $resource->id ?? null,
                        'type' => $resource->type ?? '',
                        'name' => $resource->name ?? '',
                        'description' => $resource->description ?? '',
                        'download_path' => $resource->download_path ?? '',
                    ];
                })->toArray();
            }
        } else {
            // Initialize as empty array
            $this->resources = [];
            // Start with at least one empty resource
            $this->addResource();
        }
    }

    public function addResource()
    {
        // Add a new empty resource array
        $newResource = [
            'id' => null,
            'type' => '',
            'name' => '',
            'description' => '',
            'download_path' => '',
        ];
        $this->resources[] = $newResource;
    }

    public function removeResource($index)
    {
        if (count($this->resources) > 1) {
            unset($this->resources[$index]);
            $this->resources = array_values($this->resources); // Re-index array
        }
    }

    public function handleFileUploaded($eventData)
    {
        // Extract the resource index from the fieldName (e.g., 'download_0' -> 0)
        if (preg_match('/download_(\d+)/', $eventData['fieldName'], $matches)) {
            $index = (int) $matches[1];
            if (isset($this->resources[$index])) {
                $this->resources[$index]['download_path'] = $eventData['filePath'];
                $this->dispatch('resourceUpdated');
            }
        }
    }

    public function render()
    {
        return view('livewire.project-resource-manager');
    }
}
