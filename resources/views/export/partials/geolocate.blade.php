<hr class="header mx-auto" style="width:500px;">
<form action="{{ route('admin.export.geolocate.post') }}"
      method="post" role="form" id="update-rapid-file">
    @csrf
    <input type="hidden" name="entries" value="{{ old('entries', $count) }}">
    <div id="controls">
        <div class="row default mt-5" style="display: none">
            {!! $exportSelect !!}
            {!! $groupedHeaders !!}
        </div>
        <div class="row entry mt-5">
            {!! $exportSelect !!}
            {!! $groupedHeaders !!}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 mt-5 text-left">
            <button type="button" class="btn btn-primary pl-4 pr-4 btn-add" data-hover="tooltip" title="{{ t('Add New Row') }}"><i
                        class="fas fa-plus"></i></button>
            <button type="button" class="btn btn-primary pl-4 pr-4 btn-remove prevent-default" data-hover="tooltip"
                    title="{{ t('Delete Last Row') }}"><i
                        class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="row">
        <button type="submit"
                class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto">{{ t('Submit') }}</button>
    </div>
</form>