@foreach($form['header'] as $source => $column)
    <div data-id="{{ $source }}" class="col-sm-6 mt-3 justify-content-center input-group">
        <select class="header-select" name="exportFields[{{$i}}][{{ $source }}]"
                data-live-search="true"
                title="{{ $source }}"
                data-header="{{ t('Select...') }}"
                data-width="80%"
                data-style="btn-primary"
                required>
            <option value="">{{ t('None') }}</option>
            @foreach($column as $item)
                <option value="{{ $item }}"{{ isset($form['data']) && $form['data'][$i][$source] === $item ? ' selected': '' }}>
                    {{ $item }}</option>
            @endforeach
        </select>
    </div>
@endforeach
