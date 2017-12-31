@foreach ($groups as $group)
    @foreach ($group->projects as $project)
        <tr id="test{{ $project->id }}">
            <td><a href="{{ route('web.projects.show', [$project->id]) }}">{{ $project->title }}</a>
            </td>
            <td><a href="{{ route('web.groups.show', [$group->id]) }}">{{ $group->title }}</a></td>
            <td class="buttons-sm">
                <button title="@lang('buttons.viewTitle')" class="btn btn-primary btn-xs"
                        type="button"
                        onClick="location.href='{{ route('web.projects.show', [$project->id]) }}'"><span
                            class="fa fa-eye fa-lg"></span> @lang('buttons.view')</button>
                <button title="@lang('buttons.dataTitle')" class="btn btn-inverse btn-xs"
                        type="button"
                        onClick="location.href='{{ route('web.imports.import', [$project->id]) }}'">
                    <span class="fa fa-plus fa-lg"></span> @lang('buttons.data')</button>
                <button title="@lang('buttons.dataViewTitle')" class="btn btn-info btn-xs"
                        type="button"
                        onClick="location.href='{{ route('projects.get.explore', [$project->id]) }}'">
                    <span class="fa fa-search fa-lg"></span> @lang('buttons.dataView')</button>
                <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs"
                        type="button"
                        onClick="location.href='{{ route('web.projects.duplicate', [$project->id]) }}'">
                    <span class="fa fa-share-alt fa-lg"></span> @lang('buttons.duplicate')</button>
                <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs"
                        type="button"
                        onClick="location.href='{{ route('web.projects.edit', [$project->id]) }}'"><span
                            class="fa fa-cog fa-lg"></span> @lang('buttons.edit')</button>
                @can('delete', $group)
                    <button class="btn btn-xs btn-danger" title="@lang('buttons.deleteTitle')"
                            data-href="{{ route('web.projects.delete', [$project->id]) }}"
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
@endforeach