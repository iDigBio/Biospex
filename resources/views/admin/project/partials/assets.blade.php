@livewire('project-asset-manager', [
    'assets' => $resources ?? [],
    'projectUuid' => $project->uuid ?? null,
    'errors' => $errors->toArray() ?? null  // Convert errors to array
], key('project-assets-' . ($project->id ?? 'new')))
