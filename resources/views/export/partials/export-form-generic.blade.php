<div class="row">
    <div id="controls" class="col-sm-12 mb-5">
        @for($i=0; $i < $data['count']; $i++)
            <div class="row entry mt-2 justify-content-between">
                <input type="hidden" class="hidden" id="order{{ $i }}" name="exportFields[{{$i}}][order]" value="">
                @include('export.partials.tags-select-generic-fields')
            </div>
        @endfor
    </div>
</div>
<div class="row">
    <button type="submit" class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto">{{ t('Submit') }}</button>
</div>
<div class="row">
    <div id="duplicateWarning" class="col-sm-10 mx-auto text-center text-danger collapse">
        {{ t('Field select dropdowns cannot contain duplicate values.') }}
    </div>
</div>
