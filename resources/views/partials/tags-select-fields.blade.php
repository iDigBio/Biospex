@if(isset($data['frmData']))
    @foreach($data['frmData'][$i]['order'] as $index => $column)
        <div data-id="{{ $index }}" data-count="{{ $i }}" class="col-sm-2 p-0 sort input-group">
            <div class="input-group-prepend ui-draggable-handle">
                <span class="input-group-text"><i class="fa fa-grip"></i></span>
            </div>
            <select name="exportFields[{{$i}}][{{ $index }}]"
                    data-live-search="true"
                    data-actions-box="true"
                    title="{{ $index }}"
                    data-header="{{ t('Select...') }}"
                    data-width="180"
                    data-style="btn-primary">
                @foreach($column as $item)
                    <option value="{{ $item }}"{{ $data['frmData'][$i][$index] === $item ? ' selected': '' }}>{{ $item }}</option>
                @endforeach
            </select>
        </div>
    @endforeach
@else
    @foreach($data['tags'] as $index => $column)
        <div data-id="{{ $index }}" data-count="{{ $i }}" class="col-sm-2 p-0 sort input-group">
            <div class="input-group-prepend ui-draggable-handle">
                <span class="input-group-text"><i class="fa fa-grip"></i></span>
            </div>
            <select name="exportFields[{{$i}}][{{ $index }}]"
                    data-live-search="true"
                    data-actions-box="true"
                    title="{{ $index }}"
                    data-header="{{ t('Select...') }}"
                    data-width="180"
                    data-style="btn-primary">
                @foreach($column as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                @endforeach
            </select>
        </div>
    @endforeach
@endif