<form action="{{ route('admin.geolocate.store', [$expedition->project_id, $expedition->id]) }}"
      method="post" role="form" class="geolocateFrm">
    @csrf
    <input type="hidden" id="entries" name="entries" value="{{ old('entries', isset($data['count'])) ? $data['count'] : 0 }}">
    @isset($data['frmName'])
        @include('admin.geolocate.partials.delete')
    @endisset
    <div class="row">
        <div class="col-sm-10 mx-auto text-center">
            @include('admin.geolocate.partials.source-type')
        </div>
    </div>
    @include('admin.geolocate.partials.form-fields')
</form>
<div class="row default mt-2" style="display: none">
    <input type="hidden" class="hidden" id="order" data-id="999" name="exportFields[999][order]" value="">
    @include('admin.geolocate.partials.geolocate-field-select-default')
    @include('admin.geolocate.partials.header-field-select-default')
</div>
