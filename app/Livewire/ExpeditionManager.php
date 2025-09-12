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

class ExpeditionManager extends Component
{
    public $logo_path = '';

    public $expeditionUuid = null;

    public $projectUuid = null;

    protected $listeners = [
        'fileUploaded' => 'handleFileUploaded',
    ];

    public function mount($logo_path = null, $expeditionUuid = null, $projectUuid = null)
    {
        $this->logo_path = $logo_path ?? '';
        $this->expeditionUuid = $expeditionUuid;
        $this->projectUuid = $projectUuid;
    }

    public function handleFileUploaded($eventData)
    {
        // Handle logo uploads for Expedition model
        if ($eventData['fieldName'] === 'logo_path' && $eventData['modelType'] === 'Expedition') {
            $this->logo_path = $eventData['filePath'];
            $this->dispatch('logoPathUpdated', $eventData['filePath']);
        }
    }

    public function render()
    {
        return view('livewire.expedition-manager');
    }
}
