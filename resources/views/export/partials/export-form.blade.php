<hr class="header mx-auto" style="width:500px;">
<form action="{{ route('admin.export.create', [$destination]) }}"
      method="post" role="form" class="exportFrm">
    @csrf
    <input type="hidden" name="entries" value="{{ old('entries', $data['count']) }}">
    <input type="hidden" name="exportDestination" value="{{ $destination }}">
    <div class="row">
        <div class="col-sm-10 mx-auto text-center">
            @include('partials.export-type')
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-sm-2 mx-auto font-weight-bold text-center">{{ t('Field') }}</div>
        <div class="col-sm-10 text-left font-weight-bold">{{ t('Grab edge of drop downs and drag in order to sort order preference for selecting data.') }}</div>
    </div>
    <div class="row">
        <div id="controls" class="col-sm-12">
            @for($i=0; $i < $data['count']; $i++)
            <div class="row entry mt-2">
                <input type="hidden" class="hidden" id="order{{ $i }}" name="exportFields[{{$i}}][order]" value="">
                @include('export.partials.export-field-select')
                @include('partials.tags-select-fields')
            </div>
            @endfor
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 mt-5 text-left">
            <button type="button" class="btn btn-primary pl-4 pr-4 btn-add" data-hover="tooltip"
                    title="{{ t('Add New Row') }}"><i
                        class="fas fa-plus"></i></button>
            <button type="button" class="btn btn-primary pl-4 pr-4 btn-remove prevent-default" data-hover="tooltip"
                    title="{{ t('Delete Last Row') }}"><i
                        class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="row">
        <button type="submit" id="geolocateSubmit"
                class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto">{{ t('Submit') }}</button>
    </div>
    <div class="row">
            <div id="duplicateWarning" class="col-sm-10 mx-auto text-center text-danger collapse">
                {{ t('Field select dropdowns cannot contain duplicate values.') }}
            </div>
    </div>
</form>
<div class="row default mt-2" style="display: none">
    <input type="hidden" class="hidden" id="order" data-id="999" name="exportFields[999][order]" value="">
    @include('export.partials.export-field-select-default')
    @include('partials.tags-select-fields-default')
</div>