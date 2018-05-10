@foreach ($user->groups as $group)
    <tr>
        <td>{{ $group->title }}</td>
        <td class="buttons-sm">
            <button title="@lang('buttons.viewTitle')" class="btn btn-default btn-primary btn-sm"
                    type="button" onClick="location.href='{{ route('web.groups.show', [$group->id]) }}'">
                <span class="fa fa-eye fa-lrg"></span> @lang('buttons.view')</button>
            @can('isOwner', $group)
                <button title="@lang('buttons.editTitle')" class="btn btn-default btn-warning btn-sm"
                        type="button"
                        onClick="location.href='{{ route('web.groups.edit', array($group->id)) }}'"><span
                            class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
                <button title="@lang('buttons.inviteTitle')" class="btn btn-default btn-reverse btn-sm"
                        type="button"
                        onClick="location.href='{{ route('web.invites.index', [$group->id]) }}'"><span
                            class="fa fa-users fa-lrg"></span> @lang('buttons.invite')</button>
            @endcan
            @can('isOwner', $group)
                <button class="btn btn-sm btn-danger" title="@lang('buttons.deleteTitle')"
                        data-href="{{ route('web.groups.delete', array($group->id)) }}"
                        data-method="delete"
                        data-toggle="confirmation"
                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                        data-btn-ok-class="btn-success"
                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                        data-btn-cancel-class="btn-danger"
                        data-title="Continue action?" data-content="This will trash the item">
                    <span class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')
                </button>
            @endcan
        </td>
    </tr>
@endforeach