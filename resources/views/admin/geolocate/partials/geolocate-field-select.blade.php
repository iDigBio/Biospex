<div class="col-sm-6 mt-3">
    <select class="geolocate-field" name="fields[{{$i}}][geo]"
            data-live-search="true"
            data-actions-box="true"
            data-header="{{ t('Select GeoLocate Field ( * required)') }}"
            data-width="80%"
            data-style="btn-primary"
            required>
        <option value="">{{ t('None') }}</option>
        @foreach($form['geo'] as $key => $value)
            <option value="{{ is_numeric($key) ? str_replace('*', '', $value) : $key }}"
                    {{ isset($form['fields']) && $form['fields'][$i]['geo'] === (is_numeric($key) ?
                        str_replace('*', '', $value) : $key) ? ' selected': '' }}>
                {{ $value }}</option>
        @endforeach
    </select>
</div>