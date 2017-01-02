@foreach($trashed as $expedition)
    <tr>
        <td>{{ $expedition->id }}</td>
        <td>{{ $expedition->title }}</td>
        <td>
            <div class="btn-toolbar">
                <button title="@lang('buttons.restoreTitle')"
                        class="btn btn-success btn-xs"
                        type="button"
                        onClick="location.href='{{ route('admin.expeditions.restore', [$expedition->id]) }}'">
                    <span class="fa fa-plus fa-lg"></span><!-- @lang('buttons.view') --></button>

                <button title="@lang('buttons.deleteTitle')"
                        class="btn btn-danger btn-xs" type="button"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('admin.expeditions.destroy', [$expedition->id]) }}"
                        data-method="delete">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
            </div>
        </td>
    </tr>
@endforeach