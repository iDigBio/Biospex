<div class="col-sm-2 m-auto">
    <select class="export-field" name="exportFields[{{$i}}][field]"
            data-live-search="true"
            data-actions-box="true"
            title="{{ t('Field') }}"
            data-header="{{ t('Select Export Field') }}"
            data-width="200"
            data-style="btn-primary"
            required>
        <option value="">{{ t('None') }}</option>
        @foreach($data['fields'] as $value)
            <option value="{{ str_replace('*', '', $value) }}"
                    {{ isset($data['frmData']) && $data['frmData'][$i]['field'] === str_replace('*', '', $value) ? ' selected': '' }}>
                {{ $value }}</option>
        @endforeach
    </select>
</div>