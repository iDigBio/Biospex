@livewire('project-resource-manager', [
    'resources' => $resources ?? [],
    'projectUuid' => $project->uuid ?? null,
    'errors' => $errors->toArray() ?? null  // Convert errors to array
], key('project-resources-' . ($project->id ?? 'new')))
