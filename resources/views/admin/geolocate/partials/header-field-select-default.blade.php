<div class="col-sm-6 mt-3">
    <select class="header-select-default" name="fields[999][csv]"
            data-live-search="true"
            data-actions-box="true"
            data-header="{{ t('Select CSV Column') }}"
            data-width="80%"
            data-style="btn-primary">
        <option value="">{{ t('None') }}</option>
        @foreach($form['csv'] as $column)
            <option value="{{ $column }}">{{ $column }}</option>
        @endforeach
    </select>
</div>
