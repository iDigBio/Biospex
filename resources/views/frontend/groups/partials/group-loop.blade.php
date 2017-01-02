@foreach ($user->groups as $group)
    <tr>
        <td>{{ $group->title }}</td>
        <td class="buttons-sm">
            <button title="@lang('buttons.viewTitle')" class="btn btn-default btn-primary btn-sm"
                    type="button" onClick="location.href='{{ route('web.groups.show', [$group->id]) }}'">
                <span class="fa fa-eye fa-lrg"></span> @lang('buttons.view')</button>
            @can('update', $group)
                <button title="@lang('buttons.editTitle')" class="btn btn-default btn-warning btn-sm"
                        type="button"
                        onClick="location.href='{{ route('web.groups.edit', array($group->id)) }}'"><span
                            class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
                <button title="@lang('buttons.inviteTitle')" class="btn btn-default btn-reverse btn-sm"
                        type="button"
                        onClick="location.href='{{ route('web.invites.index', [$group->id]) }}'"><span
                            class="fa fa-users fa-lrg"></span> @lang('buttons.invite')</button>
            @endcan
            @can('delete', $group)
                <button title="@lang('buttons.deleteTitle')"
                        class="btn btn-danger btn-sm" type="button"
                        data-method="delete"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('web.groups.delete', array($group->id)) }}"><span
                            class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
            @endcan
        </td>
    </tr>
@endforeach