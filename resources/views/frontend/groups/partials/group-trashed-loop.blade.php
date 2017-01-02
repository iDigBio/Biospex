@foreach ($trashed->trashedGroups as $group)
    <tr>
        <td>{{ $group->title }}</td>
        <td class="buttons-sm">
            <button title="@lang('buttons.restoreTitle')"
                    class="btn btn-success btn-sm"
                    type="button"
                    onClick="location.href='{{ route('web.groups.restore', [$group->id]) }}'">
                <span class="fa fa-plus fa-lg"></span> @lang('buttons.restore') </button>
            @can('delete', $group)
                <button title="@lang('buttons.deleteTitle')"
                        class="btn btn-danger btn-sm" type="button"
                        data-method="delete"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('web.groups.destroy', array($group->id)) }}"><span
                            class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
            @endcan
        </td>
    </tr>
@endforeach