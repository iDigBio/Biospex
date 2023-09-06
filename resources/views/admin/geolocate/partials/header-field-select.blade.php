<div class="col-sm-6 mt-3">
    <select class="header-select" name="fields[{{$i}}][csv]"
            data-live-search="true"
            data-header="{{ t('Select CSV Column') }}"
            data-width="80%"
            data-style="btn-primary"
            required>
        <option value="">{{ t('None') }}</option>
        @foreach($form['csv'] as $column)
            <option value="{{ $column }}"{{ isset($form['fields']) && $form['fields'][$i]['csv'] === $column ? ' selected': '' }}>
                {{ $column }}</option>
        @endforeach
    </select>
</div>
