<hr class="header mx-auto" style="width:500px;">
<form action=""
      method="post" role="form" class="exportFrm">
    @csrf
    <input type="hidden" name="entries" value="{{ old('entries', isset($data['count'])) ? $data['count'] : 0 }}">
    <input type="hidden" name="exportDestination" value="">
    @isset($data['frmName'])
        @include('admin.geolocate.partials.export-delete')
    @endisset
    <div class="row">
        <div class="col-sm-10 mx-auto text-center">
            @include('admin.geolocate.partials.source-type')
        </div>
    </div>
    @include('admin.geolocate.partials.export-form-fields')
</form>
<div class="row default mt-2" style="display: none">
    <input type="hidden" class="hidden" id="order" data-id="999" name="exportFields[999][order]" value="">
    @include('admin.geolocate.partials.export-field-select-default')
    @include('admin.geolocate.partials.header-select-fields-default')
</div>