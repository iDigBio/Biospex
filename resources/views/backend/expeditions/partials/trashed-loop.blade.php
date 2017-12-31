@foreach($trashed as $expedition)
    <tr>
        <td>{{ $expedition->id }}</td>
        <td>{{ $expedition->title }}</td>
        <td>
            <div class="btn-toolbar">
                <button title="@lang('buttons.restoreTitle')"
                        class="btn btn-success btn-xs"
                        type="button"
                        onClick="location.href='{{ route('admin.expeditions.restore', [$expedition->id]) }}'">
                    <span class="fa fa-plus fa-lg"></span><!-- @lang('buttons.view') --></button>

                <button class="btn btn-xs btn-danger" title="@lang('buttons.deleteTitle')"
                        data-href="{{ route('admin.expeditions.destroy', [$expedition->id]) }}"
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