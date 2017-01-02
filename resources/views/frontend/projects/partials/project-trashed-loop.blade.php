@foreach ($trashed as $group)
    @foreach ($group->trashedProjects as $project)
        <tr id="test{{ $project->id }}">
            <td>{{ $project->title }}</td>
            <td><a href="{{ route('web.groups.show', [$group->id]) }}">{{ $group->title }}</a></td>
            <td class="buttons-sm">
                <button title="@lang('buttons.restoreTitle')"
                        class="btn btn-success btn-xs"
                        type="button"
                        onClick="location.href='{{ route('web.projects.restore', [$project->id]) }}'">
                    <span class="fa fa-plus fa-lg"></span> @lang('buttons.restore') </button>
                @can('delete', $group)
                <button title="@lang('buttons.destroyTitle')"
                        class="btn btn-danger btn-xs"
                        data-method="delete"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('web.projects.destroy', [$project->id]) }}"><span
                            class="fa fa-trash fa-lrg"></span> @lang('buttons.destroy')
                </button>
                @endcan
            </td>
        </tr>
    @endforeach
@endforeach