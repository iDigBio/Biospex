@if(isset($data['frmData']))
    @foreach($data['frmData'][$i]['order'] as $index => $column)
        <div data-id="{{ $index }}" class="col-sm-2 p-0 sort input-group">
            <div class="input-group-prepend ui-draggable-handle">
                <span class="input-group-text"><i class="fa fa-grip"></i></span>
            </div>
            <select name="exportFields[{{$i}}][{{ $index }}][]"
                    multiple data-actions-box="true"
                    data-live-search="true"
                    title="{{ $index }}"
                    data-header="{{ t('Select...') }}"
                    data-width="180"
                    data-style="btn-primary">
                @foreach($column as $item)
                    <option value="{{ $item }}"{{ in_array($item, $data['frmData'][$i][$index]) ? ' selected': '' }}>{{ $item }}</option>
                @endforeach
            </select>
        </div>
    @endforeach
@else
    @foreach($data['header'] as $index => $column)
        <div data-id="{{ $index }}" class="col-sm-2 p-0 sort input-group">
            <div class="input-group-prepend ui-draggable-handle">
                <span class="input-group-text"><i class="fa fa-grip"></i></span>
            </div>
            <select class="tag-select" name="exportFields[{{$i}}][{{ $index }}][]"
                    multiple data-actions-box="true"
                    data-live-search="true"
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