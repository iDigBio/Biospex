<tr>
    <td>{{ $expedition->title }}</td>
    <td>{{ $expedition->description }}</td>
    <td>{{ DateHelper::formatDate($expedition->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
    <td class="fit">
        <button title="@lang('buttons.restoreTitle')"
                class="btn btn-success btn-xs"
                type="button"
                onClick="location.href='{{ route('web.expeditions.restore', [$project->id, $expedition->id]) }}'">
            <span class="fa fa-plus fa-lg"></span><!-- @lang('buttons.view') --></button>
        @can('isOwner', $project->group)
        <button class="btn btn-xs btn-danger" title="@lang('buttons.deleteTitle')"
                data-href="{{ route('web.expeditions.destroy', [$project->id, $expedition->id]) }}"
                data-method="delete"
                data-toggle="confirmation"
                data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                data-btn-ok-class="btn-success"
                data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                data-btn-cancel-class="btn-danger"
                data-title="Continue action?" data-content="This will destroy the item">
            <span class="fa fa-remove fa-lrg"></span> <!-- @lang('buttons.delete') -->
        </button>
        @endcan
    </td>
</tr>