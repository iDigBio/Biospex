@foreach($notices as $notice)
    <tr>
        <td>{{ $notice->message }}</td>
        <td>
            @if($notice->enabled)
                <button class="label label-success" onClick="location.href='{{ route('admin.notices.disable', [$notice->id]) }}'">Enabled</button>
            @else
                <button class="label label-danger" onClick="location.href='{{ route('admin.notices.enable', [$notice->id]) }}'">Disabled</button>
            @endif
        </td>
        <td class="button-fix">
            <div class="btn-toolbar">
                <button title="Edit Notice" class="btn btn-warning btn-xs" type="button"
                        onClick="location.href='{{ route('admin.notices.edit', [$notice->id]) }}'">
                    <span class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') --></button>

                <button title="Delete Notice" class="btn btn-danger btn-xs" type="button"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('admin.notices.delete', [$notice->id]) }}"
                        data-method="delete">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
            </div>
        </td>
    </tr>
@endforeach