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

namespace Tests\Feature\Components\Livewire;

use App\Livewire\ProjectManager;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectManagerTest extends TestCase
{
    /** @test */
    public function it_can_mount_with_default_values()
    {
        Livewire::test(ProjectManager::class)
            ->assertSet('logo_path', '')
            ->assertSet('projectUuid', null);
    }

    /** @test */
    public function it_can_mount_with_provided_values()
    {
        $logoPath = 'projects/logos/test-logo.png';
        $projectUuid = 'test-uuid-123';

        Livewire::test(ProjectManager::class, [
            'logo_path' => $logoPath,
            'projectUuid' => $projectUuid,
        ])
            ->assertSet('logo_path', $logoPath)
            ->assertSet('projectUuid', $projectUuid);
    }

    /** @test */
    public function it_handles_file_uploaded_event_for_project_logo()
    {
        $filePath = 'projects/logos/uploaded-logo.png';
        $eventData = [
            'fieldName' => 'logo_path',
            'filePath' => $filePath,
            'modelType' => 'Project',
        ];

        Livewire::test(ProjectManager::class)
            ->call('handleFileUploaded', $eventData)
            ->assertSet('logo_path', $filePath)
            ->assertDispatched('logoPathUpdated', $filePath);
    }

    /** @test */
    public function it_ignores_file_uploaded_event_for_wrong_field_name()
    {
        $eventData = [
            'fieldName' => 'other_field',
            'filePath' => 'projects/logos/uploaded-logo.png',
            'modelType' => 'Project',
        ];

        Livewire::test(ProjectManager::class)
            ->call('handleFileUploaded', $eventData)
            ->assertSet('logo_path', '') // Should remain empty
            ->assertNotDispatched('logoPathUpdated');
    }

    /** @test */
    public function it_ignores_file_uploaded_event_for_wrong_model_type()
    {
        $eventData = [
            'fieldName' => 'logo_path',
            'filePath' => 'projects/logos/uploaded-logo.png',
            'modelType' => 'Expedition',
        ];

        Livewire::test(ProjectManager::class)
            ->call('handleFileUploaded', $eventData)
            ->assertSet('logo_path', '') // Should remain empty
            ->assertNotDispatched('logoPathUpdated');
    }

    /** @test */
    public function it_renders_the_view()
    {
        Livewire::test(ProjectManager::class)
            ->assertSee('logo_path', false); // Check for hidden input in view
    }
}
