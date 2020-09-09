@for($i=0; $i < $count; $i++)
    @foreach($mapped as $index => $column)
        <div class="col-sm-2 m-auto">
            <select name="exportFields[{{$i}}][{{ $index }}]"
                    data-live-search="true"
                    data-actions-box="true"
                    title="{{ $index }}"
                    data-header="Select a column"
                    data-width="200"
                    data-style="btn-primary">
                @foreach($column as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                @endforeach
            </select>
        </div>
    @endforeach
@endfor