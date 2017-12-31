@foreach ($elements as $item)
    @if (preg_match('/\.json/i', $item->nodeValue))
    <tr id="{{ $item->nodeValue }}">
        <td>
            {!! Form::checkbox('files[]', $item->nodeValue, null, ['id' => $item->nodeValue, 'class' => 'checkbox-all']) !!}
        </td>
        <td>{!! $item->nodeValue !!}</td>
        <td><td class="button-fix">
            <div class="btn-toolbar">
                <button class="btn btn-xs btn-danger" title="@lang('buttons.deleteTitle')"
                        data-href="{{ route('admin.ocr.delete', [$item->nodeValue]) }}"
                        data-method="delete"
                        data-toggle="confirmation"
                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                        data-btn-ok-class="btn-success"
                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                        data-btn-cancel-class="btn-danger"
                        data-title="Continue action?" data-content="This will destroy the item">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
                </button>
            </div>
        </td>
    </tr>
    @endif
@endforeach