@foreach ($notifications as $notification)
    <tr>
        <td>{{ $notification->title }}</td>
        <td>{{ $notification->message }}</td>
        <td>
                <button title="@lang('buttons.deleteTitle')"
                        class="btn btn-danger btn-sm" type="button"
                        data-method="delete"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('web.notifications.delete', array($notification->id)) }}"><span
                            class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
        </td>
    </tr>
@endforeach