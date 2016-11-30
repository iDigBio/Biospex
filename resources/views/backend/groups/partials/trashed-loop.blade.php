@foreach($trashed as $group)
    <tr>
        <td>{{ $group->id }}</td>
        <td>{{ $group->name }}</td>
        <td>
            <div class="btn-toolbar">
                <button title="@lang('buttons.restoreTitle')"
                        class="btn btn-success btn-xs"
                        type="button"
                        onClick="location.href='{{ route('admin.groups.restore', [$group->id]) }}'">
                    <span class="fa fa-plus fa-lg"></span><!-- @lang('buttons.view') --></button>

                <button title="@lang('buttons.deleteTitle')"
                        class="btn btn-danger btn-xs" type="button"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('admin.groups.trash', [$group->id]) }}"
                        data-method="delete">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
            </div>
        </td>
    </tr>
@endforeach