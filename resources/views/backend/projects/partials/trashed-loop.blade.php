@foreach($trashed as $project)
    <tr>
        <td>{{ $project->id }}</td>
        <td>{{ $project->title }}</td>
        <td>
            <div class="btn-toolbar">
                <button title="@lang('pages.restoreTitle')"
                        class="btn btn-success btn-xs"
                        type="button"
                        onClick="location.href='{{ route('admin.projects.restore', [$project->id]) }}'">
                    <span class="fa fa-plus fa-lg"></span><!-- @lang('pages.view') --></button>

                <button class="btn btn-xs btn-danger" title="@lang('pages.deleteTitle')"
                        data-href="{{ route('admin.projects.destroy', [$project->id]) }}"
                        data-method="delete"
                        data-toggle="confirmation"
                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                        data-btn-ok-class="btn-success"
                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                        data-btn-cancel-class="btn-danger"
                        data-title="Continue action?" data-content="This will destroy the item">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('pages.delete') -->
                </button>
            </div>
        </td>
    </tr>
@endforeach