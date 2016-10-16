@foreach ($elements as $item)
    @if (preg_match('/\.json/i', $item->nodeValue))
    <tr id="{{ $item->nodeValue }}">
        <td>
            {!! Form::checkbox('files[]', $item->nodeValue, null, ['id' => $item->nodeValue, 'class' => 'checkbox-all']) !!}
        </td>
        <td>{!! $item->nodeValue !!}</td>
        <td><td class="button-fix">
            <div class="btn-toolbar">
                <button title="@lang('buttons.deleteTitle')" class="btn btn-danger btn-xs" type="button"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('admin.ocr.delete', [$item->nodeValue]) }}"
                        data-method="delete">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
            </div>
        </td>
    </tr>
    @endif
@endforeach