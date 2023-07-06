@foreach($frmData['header'] as $index => $column)
    <div data-id="{{ $index }}" class="col-sm-6 mt-3 justify-content-center input-group">
        <select class="header-select-default" name="exportFields[999][{{ $index }}]"
                data-live-search="true"
                data-actions-box="true"
                title="{{ $index }}"
                data-header="{{ t('Select...') }}"
                data-width="80%"
                data-style="btn-primary">
            <option value="">{{ t('None') }}</option>
            @foreach($column as $item)
                <option value="{{ $item }}">{{ $item }}</option>
            @endforeach
        </select>
    </div>
@endforeach