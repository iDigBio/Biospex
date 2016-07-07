@foreach($workflows as $workflow)
    <tr>
        <td>{{ $workflow->title }}</td>
        <td>
            @if($workflow->enabled)
                <button class="label label-success" onClick="location.href='{{ route('admin.workflows.disable', [$workflow->id]) }}'">Enabled</button>
            @else
                <button class="label label-danger" onClick="location.href='{{ route('admin.workflows.enable', [$workflow->id]) }}'">Disabled</button>
            @endif
        </td>
        <td><td class="button-fix">
            <div class="btn-toolbar">
                <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                        onClick="location.href='{{ route('admin.workflows.edit', [$workflow->id]) }}'">
                    <span class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') --></button>

                <button title="@lang('buttons.deleteTitle')" class="btn btn-danger btn-xs" type="button"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('admin.workflows.delete', [$workflow->id]) }}"
                        data-method="delete">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
            </div>
        </td>
    </tr>
@endforeach