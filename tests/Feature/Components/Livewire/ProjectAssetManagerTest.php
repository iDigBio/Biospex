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

use App\Livewire\ProjectAssetManager;
use App\Models\Project;
use App\Models\ProjectAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->project = Project::factory()->create();
    $this->projectUuid = $this->project->uuid;
});

describe('ProjectAssetManager Component', function () {

    it('mounts with empty resources collection', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => $this->projectUuid, // Changed from 'project' to 'projectUuid'
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('assets', [['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']])
            ->assertSet('projectUuid', $this->projectUuid) // Changed assertion
            ->assertSet('errors', null);
    });

    it('mounts with existing resources', function () {
        $projectAsset = ProjectAsset::factory()->create([
            'project_id' => $this->project->id,
            'type' => 'Dataset',
            'name' => 'Test Resource',
            'description' => 'Test Description',
        ]);

        $assets = collect([$projectAsset]);

        Livewire::test(ProjectAssetManager::class, [
            'assets' => $assets,
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('assets', [[
                'id' => $projectAsset->id,
                'type' => 'Dataset',
                'name' => 'Test Resource',
                'description' => 'Test Description',
                'download_path' => $projectAsset->download_path,
            ]]);
    });

    it('mounts with null resources parameter', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => null,
            'projectUuid' => $this->projectUuid, // Changed from 'project'
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('assets', [['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']])
            ->assertCount('assets', 1);
    });

    // Update all other test methods to use 'projectUuid' instead of 'project'
    it('can add new site-asset', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => $this->projectUuid, // Changed
            'errors' => null,
        ])
            ->call('addAsset')
            ->assertCount('assets', 2)
            ->assertSet('assets.1', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']);
    });

    it('can remove site-asset when more than one exists', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->call('addAsset') // Should have 2 assets now
            ->assertCount('assets', 2)
            ->call('removeAsset', 0)
            ->assertCount('assets', 1);
    });

    it('cannot remove site-asset when only one exists', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertCount('assets', 1)
            ->call('removeAsset', 0)
            ->assertCount('assets', 1); // Should still have 1 site-asset
    });

    it('handles file upload correctly', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'download_0',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('assets.0.download_path', 'uploads/test-file.pdf');
    });

    it('ignores file upload with invalid field name', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'invalid_field',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('assets.0.download_path', ''); // Should remain empty
    });

    it('ignores file upload for non-existent site-asset index', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'download_99',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('assets.0.download_path', ''); // Should remain empty
    });

    it('handles null errors correctly', function () {
        Livewire::test(ProjectAssetManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('errors', null);
    });

    it('renders the correct view', function () {
        Livewire::test(ProjectAssetManager::class, [
            'resources' => collect(),
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertViewIs('livewire.project-asset-manager');
    });

    it('preserves site-asset data types correctly', function () {
        $projectAsset = ProjectAsset::factory()->create([
            'project_id' => $this->project->id,
            'type' => 'Dataset',
            'name' => 'Test Resource',
            'description' => 'Test Description',
        ]);

        $resources = collect([$projectAsset]);

        $component = Livewire::test(ProjectAssetManager::class, [
            'assets' => $resources,
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ]);

        // Verify all fields are properly converted to arrays and maintain their values
        expect($component->get('assets.0.id'))->toBe($projectAsset->id);
        expect($component->get('assets.0.type'))->toBe('Dataset');
        expect($component->get('assets.0.name'))->toBe('Test Resource');
        expect($component->get('assets.0.description'))->toBe('Test Description');
        expect($component->get('assets.0.download_path'))->toBe($projectAsset->download_path);
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

        Livewire::test(ProjectAssetManager::class, [
            'assets' => $resources,
            'projectUuid' => $this->projectUuid,
            'errors' => null,
        ])
            ->assertCount('assets', 2)
            ->assertSet('assets.0.name', 'Resource 1')
            ->assertSet('assets.1.name', 'Resource 2')
            ->assertSet('assets.0.type', 'Dataset')
            ->assertSet('assets.1.type', 'Publication');
    });
});

describe('ProjectAssetManager Component - Create Page Scenarios', function () {

    it('mounts with empty assets for create page (null projectUuid)', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('assets', [['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']])
            ->assertSet('projectUuid', null)
            ->assertSet('errors', null);
    });

    it('mounts with null assets parameter for create page', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => null,
            'projectUuid' => null,
            'errors' => null,
        ])
            ->assertStatus(200)
            ->assertSet('assets', [['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']])
            ->assertCount('assets', 1);
    });

    it('can add new site-asset on create page', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('addAsset')
            ->assertCount('assets', 2)
            ->assertSet('assets.1', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']);
    });

    it('can remove site-asset on create page when more than one exists', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('addAsset') // Should have 2 assets now
            ->assertCount('assets', 2)
            ->call('removeAsset', 0)
            ->assertCount('assets', 1);
    });

    it('cannot remove site-asset on create page when only one exists', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->assertCount('assets', 1)
            ->call('removeAsset', 0)
            ->assertCount('assets', 1); // Should still have 1 site-asset
    });

    it('handles file upload correctly on create page', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'download_0',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('assets.0.download_path', 'uploads/test-file.pdf');
    });

    it('ignores file upload with invalid field name on create page', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'invalid_field',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('assets.0.download_path', ''); // Should remain empty
    });

    it('ignores file upload for non-existent site-asset index on create page', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('handleFileUploaded', [
                'fieldName' => 'download_99',
                'filePath' => 'uploads/test-file.pdf',
            ])
            ->assertSet('assets.0.download_path', ''); // Should remain empty
    });

    it('handles validation errors for create page correctly', function () {
        $errors = [
            'assets.0.name' => ['The site-asset name is required.'],
            'assets.0.type' => ['The site-asset type is required.'],
        ];

        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => $errors,
        ])
            ->assertStatus(200)
            ->assertSet('errors', $errors);
    });

    it('renders the correct view for create page', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->assertViewIs('livewire.project-asset-manager');
    });

    it('maintains proper site-asset structure on create page with multiple additions', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('addAsset')
            ->call('addAsset')
            ->assertCount('assets', 3)
            ->assertSet('assets.0', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => ''])
            ->assertSet('assets.1', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => ''])
            ->assertSet('assets.2', ['id' => null, 'type' => '', 'name' => '', 'description' => '', 'download_path' => '']);
    });

    it('handles site-asset removal in middle of array on create page', function () {
        Livewire::test(ProjectAssetManager::class, [
            'assets' => collect(),
            'projectUuid' => null,
            'errors' => null,
        ])
            ->call('addAsset')
            ->call('addAsset')
            ->assertCount('assets', 3)
            ->call('removeAsset', 1) // Remove middle site-asset
            ->assertCount('assets', 2);
    });
});
