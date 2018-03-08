@foreach($trashed as $workflow)
    <tr>
        <td>{{ $workflow->title }}</td>
        <td>
            @if($workflow->enabled)
                <button class="label label-success" onClick="location.href='{{ route('admin.workflows.disable', [$workflow->id]) }}'">Enabled</button>
            @else
                <button class="label label-danger" onClick="location.href='{{ route('admin.workflows.enable', [$workflow->id]) }}'">Disabled</button>
            @endif
        </td>
        <td class="button-fix">
            <div class="btn-toolbar">
                <button title="@lang('pages.editTitle')" class="btn btn-warning btn-xs" type="button"
                        onClick="location.href='{{ route('admin.workflows.edit', [$workflow->id]) }}'">
                    <span class="fa fa-wrench fa-sm"></span> <!-- @lang('pages.edit') --></button>

                <button class="btn btn-xs btn-danger" title="@lang('pages.deleteTitle')"
                        data-href="{{ route('admin.workflows.trash', [$workflow->id]) }}"
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