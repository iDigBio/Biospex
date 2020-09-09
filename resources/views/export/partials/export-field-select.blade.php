@for($i=0; $i < $count; $i++)
<div class="col-sm-2 m-auto">
    <select class="export-field" name="exportFields[{{$i}}][field]"
            data-live-search="true"
            data-actions-box="true"
            title="{{ t('Field') }}"
            data-header="{{ t('Select Export Field') }}"
            data-width="200"
            data-style="btn-primary">
        @foreach($exportFields as $index => $column)
            <option value="{{ $index }}">{{ $index }}</option>
        @endforeach
    </select>
</div>
@endfor