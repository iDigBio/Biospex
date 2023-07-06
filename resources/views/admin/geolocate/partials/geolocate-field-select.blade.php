<div class="col-sm-6 mt-3">
    <select class="geolocate-field" name="exportFields[{{$i}}][field]"
            data-live-search="true"
            data-actions-box="true"
            title="{{ t('Field') }}"
            data-header="{{ t('Select Export Field') }}"
            data-width="200"
            data-style="btn-primary"
            required>
        <option value="">{{ t('None') }}</option>
        @foreach($frmData['fields'] as $key => $value)
            <option value="{{ is_numeric($key) ? str_replace('*', '', $value) : $key }}"
                    {{ isset($frmData['data']) && $frmData['data'][$i]['field'] === (is_numeric($key) ?
                        str_replace('*', '', $value) : $key) ? ' selected': '' }}>
                {{ $value }}</option>
        @endforeach
    </select>
</div>