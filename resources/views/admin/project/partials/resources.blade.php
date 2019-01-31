@for($i=0; $i < $resourceCount; $i++)
    <div class="entry mb-4">
        <fieldset class="row border p-2">
            <legend class="w-auto">{{ __('Resource') }} {{ $i+1 }}</legend>
            <div class="col-3">
                <label class="col-form-label">{{ __('Type') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                    <span class="input-group-text btn btn-primary btn-add px-3 py-0" id="basic-addon1"><i
                                class="fas fa-plus"></i></span>
                    </div>
                    <select name="resources[{{ $i }}][type]" id="resources[{{ $i }}][type]"
                            class="form-control custom-select">
                        @foreach($resourceOptions as $index => $name)
                            <option value="{{ $index }}"{{ $index === old("resources.$i.type", $resources[$i]->type ?? '') ? ' selected=selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-4">
                <label class="col-form-label">{{ __('URL or Name') }}</label>
                <input type="text" class="form-control" id="resources[{{ $i }}][name]"
                       name="resources[{{ $i }}][name]"
                       value="{{ old("resources.$i.name", $resources[$i]->name ?? '') }}">
            </div>
            <div class="col-5">
                <label class="col-form-label">{{ __('Description') }}</label>
                <input type="text" class="form-control" id="resources[{{ $i }}][description]"
                       name="resources[{{ $i }}][description]"
                       value="{{ old("resources.$i.description", $resources[$i]->description ?? '') }}">
            </div>
            <div class="col-6 mt-2 mx-auto">
                <div class="custom-file">
                    <label class="custom-file-label">{{ __('Choose file...') }}</label>
                    <input type="file" class="form-control custom-file-input" name="resources[{{ $i }}][download]"
                           id="resources[{{ $i }}][download]">
                </div>
                @if ( ! empty($resources[$i]->download_file_name))
                    <br>{{ __('Current File') }}: {{ $resources[$i]->download_file_name ?? '' }}
                @endif
            </div>
            <input type="hidden" id="resources[{{ $i }}][id]" name="resources[{{ $i }}][id]"
                   value="{{ old("resources.$i.id", $resources[$i]->id ?? '') }}">
        </fieldset>
    </div>
@endfor
