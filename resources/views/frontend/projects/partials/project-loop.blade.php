@foreach ($groups as $group)
    @foreach ($group->projects as $project)
        <tr id="test{{ $project->id }}">
            <td><a href="{{ route('webauth.projects.show', [$project->id]) }}">{{ $project->title }}</a>
            </td>
            <td><a href="{{ route('webauth.groups.show', [$group->id]) }}">{{ $group->title }}</a></td>
            <td class="fit buttons-sm">
                <button title="@lang('pages.viewTitle')" class="btn btn-primary btn-xs"
                        type="button"
                        onClick="location.href='{{ route('webauth.projects.show', [$project->id]) }}'"><span
                            class="fa fa-eye fa-lg"></span> @lang('pages.view')</button>
                <button title="@lang('pages.dataTitle')" class="btn btn-inverse btn-xs"
                        type="button"
                        onClick="location.href='{{ route('webauth.imports.import', [$project->id]) }}'">
                    <span class="fa fa-plus fa-lg"></span> @lang('pages.data')</button>
                <button title="@lang('pages.dataViewTitle')" class="btn btn-info btn-xs"
                        type="button"
                        onClick="location.href='{{ route('projects.get.explore', [$project->id]) }}'">
                    <span class="fa fa-search fa-lg"></span> @lang('pages.dataView')</button>
                <button title="@lang('pages.duplicateTitle')" class="btn btn-success btn-xs"
                        type="button"
                        onClick="location.href='{{ route('webauth.projects.duplicate', [$project->id]) }}'">
                    <span class="fa fa-share-alt fa-lg"></span> @lang('pages.duplicate')</button>
                <button title="@lang('pages.editTitle')" class="btn btn-warning btn-xs"
                        type="button"
                        onClick="location.href='{{ route('webauth.projects.edit', [$project->id]) }}'"><span
                            class="fa fa-cog fa-lg"></span> @lang('pages.edit')</button>
                @can('isOwner', $group)
                    <button class="btn btn-xs btn-danger" title="@lang('pages.deleteTitle')"
                            data-href="{{ route('webauth.projects.delete', [$project->id]) }}"
                            data-method="delete"
                            data-toggle="confirmation"
                            data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                            data-btn-ok-class="btn-success"
                            data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                            data-btn-cancel-class="btn-danger"
                            data-title="Continue action?" data-content="This will delete the item">
                        <span class="fa fa-remove fa-lrg"></span> @lang('pages.delete')
                    </button>
                @endcan
            </td>
        </tr>
    @endforeach
@endforeach