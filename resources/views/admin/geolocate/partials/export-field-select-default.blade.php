<div class="col-sm-2 m-auto">
    <select class="export-field-default" name="exportFields[999][field]"
            data-live-search="true"
            data-actions-box="true"
            title="{{ t('Field') }}"
            data-header="{{ t('Select Export Field') }}"
            data-width="200"
            data-style="btn-primary"
            required>
        <option value="">{{ t('None') }}</option>
        @foreach($data['fields'] as $key => $value)
            <option value="{{ is_numeric($key) ? str_replace('*', '', $value) : $key }}">{{ $value }}</option>
        @endforeach
    </select>
</div>