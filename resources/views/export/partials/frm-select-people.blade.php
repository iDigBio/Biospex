<select id="geolocateSelect" class="selectpicker form-select"
        data-url="{{ route('admin.export.show', ['people']) }}"
        data-live-search="true"
        data-actions-box="true"
        data-target="#people"
        title="{{ t('People Forms') }}"
        data-header="{{ t('Select New or Saved Form') }}"
        data-width="250"
        data-style="btn-primary">
    <option value="" class="text-uppercase">{{ t('New') }}</option>
    @isset($forms['people'])
        @foreach($forms['people'] as $frm)
            <option value="{{ $frm->id }}">{{ $frm->present()->form_name_user }}</option>
        @endforeach
    @endisset
</select>
