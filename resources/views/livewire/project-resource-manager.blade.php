<div class="controls">
    @foreach($resources as $index => $resource)
        <div class="entry mb-4" wire:key="resource-{{ $index }}">
            <fieldset class="row border p-2">
                <legend class="w-auto">{{ t('Resources') }} {{ $index + 1 }}</legend>
                <div class="col-3">
                    <label class="col-form-label">{{ t('Type') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text btn btn-primary px-3 py-0"
                                  wire:click="addResource"
                                  style="cursor: pointer;">
                                <i class="fas fa-plus"></i>
                            </span>
                        </div>
                        <select name="resources[{{ $index }}][type]"
                                id="resources[{{ $index }}][type]"
                                wire:model.defer="resources.{{ $index }}.type"
                                class="form-control custom-select {{ ($errors && isset($errors["resources.$index.type"])) ? 'is-invalid' : '' }}">
                            <option value="">None</option>
                            @foreach(config('config.project_resources') as $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @if(count($resources) > 1)
                            <div class="input-group-append">
                                <span class="input-group-text btn btn-danger px-3 py-0"
                                      wire:click="removeResource({{ $index }})"
                                      style="cursor: pointer;">
                                    <i class="fas fa-minus"></i>
                                </span>
                            </div>
                        @endif
                        <span class="invalid-feedback">{{ $errors && isset($errors["resources.$index.type"]) ? $errors["resources.$index.type"][0] : '' }}</span>
                    </div>
                </div>
                <div class="col-4">
                    <label class="col-form-label">{{ t('URL or Name') }}</label>
                    <input type="text"
                           class="form-control {{ ($errors && isset($errors["resources.$index.name"])) ? 'is-invalid' : '' }}"
                           id="resources[{{ $index }}][name]"
                           name="resources[{{ $index }}][name]"
                           wire:model.defer="resources.{{ $index }}.name"
                           value="{{ old("resources.$index.name", $resource['name'] ?? '') }}">
                    <span class="invalid-feedback">{{ $errors && isset($errors["resources.$index.name"]) ? $errors["resources.$index.name"][0] : '' }}</span>
                </div>
                <div class="col-5">
                    <label class="col-form-label">{{ t('Description') }}</label>
                    <input type="text"
                           class="form-control {{ ($errors && isset($errors["resources.$index.description"])) ? 'is-invalid' : '' }}"
                           id="resources[{{ $index }}][description]"
                           name="resources[{{ $index }}][description]"
                           wire:model.defer="resources.{{ $index }}.description"
                           value="{{ old("resources.$index.description", $resource['description'] ?? '') }}">
                    <span class="invalid-feedback">{{ $errors && isset($errors["resources.$index.description"]) ? $errors["resources.$index.description"][0] : '' }}</span>
                </div>
                <div class="col-6 mt-2 mx-auto">
                    @livewire('file-upload', [
                        'modelType' => 'ProjectResource', 
                        'fieldName' => 'download_' . $index, 
                        'maxSize' => 10240, 
                        'allowedTypes' => ['txt', 'doc', 'docx', 'csv', 'pdf'], 
                        'projectUuid' => $projectUuid ?? null
                    ], key('file-upload-' . $index))
                </div>
                <input type="hidden"
                       id="resources[{{ $index }}][id]"
                       name="resources[{{ $index }}][id]"
                       wire:model.defer="resources.{{ $index }}.id"
                       value="{{ old("resources.$index.id", $resource['id'] ?? '') }}">

                <!-- Hidden input for download_path to be submitted with the form -->
                <input type="hidden"
                       name="resources[{{ $index }}][download_path]"
                       value="{{ $resource['download_path'] ?? '' }}">
            </fieldset>
        </div>
    @endforeach
</div>