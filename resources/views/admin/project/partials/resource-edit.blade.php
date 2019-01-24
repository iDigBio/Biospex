<div class="entry">
    <div class="col-sm-3">
        <label class="col-form-label">{{ __('Type') }}</label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1"><i class="fas fa-plus fa-2x"></i></span>
            </div>
            <select name="resources[{{ $key }}][type]" id="group_id" class="form-control custom-select">
                @foreach($resourcesSelect as $key => $name)
                    <option value="{{ $key }}"{{ $key === $resource->type ? ' selected=selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-sm-3">
        <label class="col-form-label">{{ __('URL or Name') }}</label>
        <input type="text" class="form-control" id="name{{ $key }}" name="resources[{{ $key }}][name]" value="{{ $resource->name }}">
    </div>
    <div class="col-sm-4">
        <label class="col-form-label">{{ __('Description') }}</label>
        <input type="text" class="form-control" id="description{{ $key }}" name="resources[{{ $key }}][description]" value="{{ $resource->description }}">
    </div>
    <div class="col-sm-2">
        <label for="logo" class="custom-file-label">{{ __('File') }}</label>
        <input type="file" class="custom-file-input" name="resources[{{ $key }}][download]">
        @if ( ! empty($resource->download_file_name))
            <div class="col-sm-5 fileName">
                {{ $resource->download_file_name }}
            </div>
            <input type="hidden" name="resources[{{ $key }}][download_file_name]" value="{{ $resource->download_file_name }}">
        @endif
    </div>
    <input type="hidden" name="resources[{{ $key }}][id]" value="{{ $resource->id }}">
</div>