<select id="geolocateSelect" class="selectpicker form-select"
        data-url="{{ route('admin.export.show', ['geolocate']) }}"
        data-live-search="true"
        data-actions-box="true"
        data-target="#geolocate"
        title="{{ t('GEOLocate Forms') }}"
        data-header="{{ t('Select New or Saved Form') }}"
        data-width="250"
        data-style="btn-primary">
    <option value="" class="text-uppercase">{{ t('New') }}</option>
    @isset($forms['geolocate'])
        @foreach($forms['geolocate'] as $frm)
            <option value="{{ $frm->id }}">{{ $frm->present()->form_name_user }}</option>
        @endforeach
    @endisset
</select>
