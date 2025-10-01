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

use App\Livewire\ProjectResourceManager;
use App\Models\Project;
use App\Models\ProjectAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->project = Project::factory()->create();
    $this->projectUuid = $this->project->uuid;
});

describe('ProjectResourceManager Component', function () {

    it('mounts with empty resources collection', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid, // Changed from 'project' to 'projectUuid'
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('resources', [['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']])
            ->assertSet('projectUuid', $this->projectUuid) // Changed assertion
            ->assertSet('errors', null);
    });

    it('mounts with existing resources', function () {
        $projectResource = ProjectAsset::factory()->create([
            'project_id' => $this->project->id,
            'type' => 'Dataset',
            'name' => 'Test Resource',
            'description' => 'Test Description',
        ]);

        $resources = collect([$projectResource]);

        Livewire::test(ProjectResourceManager::class, [
            'resources' => $resources,
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('resources', [[
                'id' => $projectResource->id,
                'type' => 'Dataset',
                'name' => 'Test Resource',
                'description' => 'Test Description',
                'download_path' => '',
            ]]);
    });

    it('mounts with null resources parameter', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => null,
            'projectUuid' => $this->projectUuid, // Changed from 'project'
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('resources', [['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']])
            ->assertCount('resources', 1);
    });

    // Update all other test methods to use 'projectUuid' instead of 'project'
    it('can add new site-asset', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid, // Changed
            'errors' => null,
        ])
            ->call('addResource')
            ->assertCount('resources', 2)
            ->assertSet('resources.1', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']);
    });

    it('can remove site-asset when more than one exists', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->call('addResource') // Should have 2 resources now
            ->assertCount('resources', 2)
            ->call('removeResource', 0)
            ->assertCount('resources', 1);
    });

    it('cannot remove site-asset when only one exists', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertCount('resources', 1)
            ->call('removeResource', 0)
            ->assertCount('resources', 1); // Should still have 1 site-asset
    });

    it('handles file upload correctly', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'download_0',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('resources.0.download_path', 'uploads/test-file.pdf');
    });

    it('ignores file upload with invalid field name', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'invalid_field',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('resources.0.download_path', ''); // Should remain empty
    });

    it('ignores file upload for non-existent site-asset index', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'download_99',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('resources.0.download_path', ''); // Should remain empty
    });

    it('handles null errors correctly', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('errors', null);
    });

    it('renders the correct view', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertViewIs('livewire.project-resource-manager');
    });

    it('preserves site-asset data types correctly', function () {
        $projectResource = ProjectAsset::factory()->create([
            'project_id' => $this->project->id,
            'type' => 'Dataset',
            'name' => 'Test Resource',
            'description' => 'Test Description',
        ]);

        $resources = collect([$projectResource]);

        $component = Livewire::test(ProjectResourceManager::class, [
            'resources' => $resources,
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ]);

        // Verify all fields are properly converted to arrays and maintain their values
        expect($component->get('resources.0.id'))->toBe($projectResource->id);
        expect($component->get('resources.0.type'))->toBe('Dataset');
        expect($component->get('resources.0.name'))->toBe('Test Resource');
        expect($component->get('resources.0.description'))->toBe('Test Description');
        expect($component->get('resources.0.download_path'))->toBe('');
    });

    it('handles multiple resources correctly', function () {
        $resource1 = ProjectAsset::factory()->create([
            'project_id' => $this->project->id,
            'type' => 'Dataset',
            'name' => 'Resource 1',
            'description' => 'Description 1',
        ]);

        $resource2 = ProjectAsset::factory()->create([
            'project_id' => $this->project->id,
            'type' => 'Publication',
            'name' => 'Resource 2',
            'description' => 'Description 2',
        ]);

        $resources = collect([$resource1, $resource2]);

        Livewire::test(ProjectResourceManager::class, [
            'resources' => $resources,
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertCount('resources', 2)
            ->assertSet('resources.0.name', 'Resource 1')
            ->assertSet('resources.1.name', 'Resource 2')
            ->assertSet('resources.0.type', 'Dataset')
            ->assertSet('resources.1.type', 'Publication');
    });
});

describe('ProjectResourceManager Component - Create Page Scenarios', function () {

    it('mounts with empty resources for create page (null projectUuid)', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('resources', [['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']])
            ->assertSet('projectUuid', null)
            ->assertSet('errors', null);
    });

    it('mounts with null resources parameter for create page', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => null,
            'projectUuid' => null,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('resources', [['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']])
            ->assertCount('resources', 1);
    });

    it('can add new site-asset on create page', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('addResource')
            ->assertCount('resources', 2)
            ->assertSet('resources.1', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']);
    });

    it('can remove site-asset on create page when more than one exists', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('addResource') // Should have 2 resources now
            ->assertCount('resources', 2)
            ->call('removeResource', 0)
            ->assertCount('resources', 1);
    });

    it('cannot remove site-asset on create page when only one exists', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->assertCount('resources', 1)
            ->call('removeResource', 0)
            ->assertCount('resources', 1); // Should still have 1 site-asset
    });

    it('handles file upload correctly on create page', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'download_0',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('resources.0.download_path', 'uploads/test-file.pdf');
    });

    it('ignores file upload with invalid field name on create page', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'invalid_field',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('resources.0.download_path', ''); // Should remain empty
    });

    it('ignores file upload for non-existent site-asset index on create page', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'download_99',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('resources.0.download_path', ''); // Should remain empty
    });

    it('handles validation errors for create page correctly', function () {
        $errors = [
            'resources.0.name' => ['The site-asset name is required.'],
            'resources.0.type' => ['The site-asset type is required.'],
        ];

        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => $errors,
        ])
            ->assertStatus(200)
            ->assertSet('errors', $errors);
    });

    it('renders the correct view for create page', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->assertViewIs('livewire.project-resource-manager');
    });

    it('maintains proper site-asset structure on create page with multiple additions', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('addResource')
            ->call('addResource')
            ->assertCount('resources', 3)
            ->assertSet('resources.0', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => ''])
            ->assertSet('resources.1', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => ''])
            ->assertSet('resources.2', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']);
    });

    it('handles site-asset removal in middle of array on create page', function () {
        Livewire::test(ProjectResourceManager::class, [
            'resources' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('addResource')
            ->call('addResource')
            ->assertCount('resources', 3)
            ->call('removeResource', 1) // Remove middle site-asset
            ->assertCount('resources', 2);
    });
});
