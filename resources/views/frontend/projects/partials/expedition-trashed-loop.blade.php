<tr>
    <td>{{ $expedition->title }}</td>
    <td>{{ $expedition->description }}</td>
    <td>{{ format_date($expedition->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
    <td class="buttons-xs">
        <button title="@lang('buttons.restoreTitle')"
                class="btn btn-success btn-xs"
                type="button"
                onClick="location.href='{{ route('web.expeditions.restore', [$project->id, $expedition->id]) }}'">
            <span class="fa fa-plus fa-lg"></span><!-- @lang('buttons.view') --></button>
        <button title="@lang('buttons.destroyTitle')"
                class="btn btn-danger btn-xs"
                data-method="delete"
                data-toggle="confirmation" data-placement="left"
                data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lg"
                data-btn-ok-class="btn-success"
                data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lg"
                data-btn-cancel-class="btn-danger"
                data-title="Delete item?"
                data-href="{{ route('web.expeditions.destroy', [$project->id, $expedition->id]) }}"><span
                    class="fa fa-trash fa-lrg"></span> <!-- @lang('buttons.destroy') -->
        </button>
    </td>
</tr>