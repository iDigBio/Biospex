@foreach($trashed as $actor)
    <tr>
        <td>{{ $actor->id }}</td>
        <td>{{ $actor->title }}</td>
        <td>{{ $actor->url }}</td>
        <td>{{ $actor->class}}</td>
        <td>{!! $actor->private ? 'Private' : 'Public' !!}</td>
        <td><td class="button-fix">
            <div class="btn-toolbar">
                <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                        onClick="location.href='{{ route('admin.actors.edit', [$actor->id]) }}'">
                    <span class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') --></button>

                <button class="btn btn-xs btn-danger" title="@lang('buttons.deleteTitle')"
                        data-href="{{ route('admin.actors.trash', [$actor->id]) }}"
                        data-method="delete"
                        data-toggle="confirmation"
                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                        data-btn-ok-class="btn-success"
                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                        data-btn-cancel-class="btn-danger"
                        data-title="Continue action?" data-content="This will delete the item">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
                </button>

            </div>
        </td>
    </tr>
@endforeach