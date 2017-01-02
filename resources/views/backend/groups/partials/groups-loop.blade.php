@foreach($groups as $group)
    <tr id="{{ $group->id }}">
        <td>{!! $group->id !!}</td>
        <td>{!! $group->title !!}</td>
        <td>
        <td>
            <div class="btn-toolbar">
                <button title="@lang('buttons.viewTitle')"
                        class="btn btn-primary btn-xs"
                        type="button"
                        onClick="location.href='{{ route('admin.groups.show', [$group->id]) }}'">
                    <span class="fa fa-eye fa-lg"></span><!-- @lang('buttons.view') --></button>

                <button title="@lang('buttons.deleteTitle')"
                        class="btn btn-danger btn-xs" type="button"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('admin.groups.delete', [$group->id]) }}"
                        data-method="delete">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
            </div>
        </td>
    </tr>
@endforeach