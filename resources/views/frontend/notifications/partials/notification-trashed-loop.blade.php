@foreach ($trashed as $notification)
    <tr>
        <td>{{ $notification->title }}</td>
        <td>{{ $notification->message }}</td>
        <td class="buttons-smxs">
            <button title="@lang('buttons.restoreTitle')"
                    class="btn btn-success btn-sm"
                    type="button"
                    onClick="location.href='{{ route('web.notifications.restore', [$notification->id]) }}'">
                <span class="fa fa-plus fa-lg"></span> @lang('buttons.restore') </button>
            <button title="@lang('buttons.deleteTitle')"
                    class="btn btn-danger btn-sm" type="button"
                    data-method="delete"
                    data-toggle="confirmation" data-placement="left"
                    data-href="{{ route('web.notifications.destroy', array($notification->id)) }}"><span
                        class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
        </td>
    </tr>
@endforeach