<select name="geolocateSelect" id="geolocateSelect" class="selectpicker"
        data-url="{{ route('admin.export.geolocate') }}"
        data-live-search="true"
        data-actions-box="true"
        title="{{ t('Forms') }}"
        data-header="{{ t('Select New or Saved Form') }}"
        data-width="250"
        data-style="btn-primary">
    <option value="" class="text-uppercase">{{ t('New') }}</option>
    @foreach($geolocateFrms as $frm)
        <option value="{{ $frm->id }}">{{ $frm->present()->form_name }}</option>
    @endforeach
</select>
