@foreach($data['tags'] as $index => $column)
    <div data-id="{{ $index }}" class="col-sm-2 m-auto p-0 sort input-group">
        <div class="input-group-prepend ui-draggable-handle">
            <span class="input-group-text"><i class="fa fa-grip"></i></span>
        </div>
        <select class="tag-select" name="exportFields[999][{{ $index }}]"
                data-live-search="true"
                data-actions-box="true"
                title="{{ $index }}"
                data-header="{{ t('Select...') }}"
                data-width="180"
                data-style="btn-primary">
            <option value="">{{ t('None') }}</option>
            @foreach($column as $item)
                <option value="{{ $item }}">{{ $item }}</option>
            @endforeach
        </select>
    </div>
@endforeach