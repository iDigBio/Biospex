@for($i=0; $i < (isset($resources) && $resources->isNotEmpty() ? $resources->count() : 1); $i++)
    <div class="entry mb-4">
        <fieldset class="row border p-2">
            <legend class="w-auto">{{ t('Resources') }} {{ $i+1 }}</legend>
            <div class="col-3">
                <label class="col-form-label">{{ t('Type') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                    <span class="input-group-text btn btn-primary btn-add px-3 py-0" id="basic-addon{{$i}}"><i
                                class="fas fa-plus"></i></span>
                    </div>
                    <select name="resources[{{ $i }}][type]"
                            id="resources[{{ $i }}][type]"
                            class="form-control custom-select {{ ($errors->has("resources.$i.type")) ? 'is-invalid' : '' }}">
                        <option value="">None</option>
                        @foreach(config('config.project_resources') as $name)
                            <option value="{{ $name }}"{{ $name === old("resources.$i.type", $resources[$i]->type ?? '') ? ' selected=selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <span class="invalid-feedback">{{ $errors->first("resources.$i.type") }}</span>
                </div>
            </div>
            <div class="col-4">
                <label class="col-form-label">{{ t('URL or Name') }}</label>
                <input type="text" class="form-control {{ ($errors->has("resources.$i.name")) ? 'is-invalid' : '' }}"
                       id="resources[{{ $i }}][name]"
                       name="resources[{{ $i }}][name]"
                       value="{{ old("resources.$i.name", $resources[$i]->name ?? '') }}">
                <span class="invalid-feedback">{{ $errors->first("resources.$i.name") }}</span>
            </div>
            <div class="col-5">
                <label class="col-form-label">{{ t('Description') }}</label>
                <input type="text"
                       class="form-control {{ ($errors->has("resources.$i.description")) ? 'is-invalid' : '' }}"
                       id="resources[{{ $i }}][description]"
                       name="resources[{{ $i }}][description]"
                       value="{{ old("resources.$i.description", $resources[$i]->description ?? '') }}">
                <span class="invalid-feedback">{{ $errors->first("resources.$i.description") }}</span>
            </div>
            <div class="col-6 mt-2 mx-auto">
                <div class="custom-file">
                    <label class="custom-file-label">{{ $resources[$i]->download_file_name ?? t('Choose file...') }}</label>
                    <input type="file"
                           class="form-control custom-file-input {{ ($errors->has("resources.$i.download")) ? 'is-invalid' : '' }}"
                           name="resources[{{ $i }}][download]"
                           id="resources[{{ $i }}][download]">
                    <span class="invalid-feedback">{{ $errors->first("resources.$i.download") }}</span>
                </div>

            </div>
            <input type="hidden" id="resources[{{ $i }}][id]" name="resources[{{ $i }}][id]"
                   value="{{ old("resources.$i.id", $resources[$i]->id ?? '') }}">
        </fieldset>
    </div>
@endfor
