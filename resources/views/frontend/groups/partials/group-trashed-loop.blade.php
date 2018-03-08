@foreach ($trashed->groupsTrashed as $group)
    <tr>
        <td>{{ $group->title }}</td>
        <td class="buttons-sm">
            <button title="@lang('pages.restoreTitle')"
                    class="btn btn-success btn-sm"
                    type="button"
                    onClick="location.href='{{ route('webauth.groups.restore', [$group->id]) }}'">
                <span class="fa fa-plus fa-lg"></span> @lang('pages.restore') </button>
            @can('delete', $group)
                <button class="btn btn-sm btn-danger" title="@lang('pages.deleteTitle')"
                        data-href="{{ route('webauth.groups.destroy', array($group->id)) }}"
                        data-method="delete"
                        data-toggle="confirmation"
                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                        data-btn-ok-class="btn-success"
                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                        data-btn-cancel-class="btn-danger"
                        data-title="Continue action?" data-content="This will destroy the item">
                    <span class="fa fa-remove fa-lrg"></span> @lang('pages.delete')
                </button>

            @endcan
        </td>
    </tr>
@endforeach