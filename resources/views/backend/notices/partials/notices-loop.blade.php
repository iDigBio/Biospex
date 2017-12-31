@foreach($notices as $notice)
    <tr>
        <td>{{ $notice->message }}</td>
        <td>
            @if($notice->enabled)
                <button class="label label-success" onClick="location.href='{{ route('admin.notices.disable', [$notice->id]) }}'">Enabled</button>
            @else
                <button class="label label-danger" onClick="location.href='{{ route('admin.notices.enable', [$notice->id]) }}'">Disabled</button>
            @endif
        </td>
        <td class="button-fix">
            <div class="btn-toolbar">
                <button title="Edit Notice" class="btn btn-warning btn-xs" type="button"
                        onClick="location.href='{{ route('admin.notices.edit', [$notice->id]) }}'">
                    <span class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') --></button>
                <button class="btn btn-xs btn-danger" title="@lang('buttons.deleteTitle')"
                        data-href="{{ route('admin.notices.delete', [$notice->id]) }}"
                        data-method="delete"
                        data-toggle="confirmation"
                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                        data-btn-ok-class="btn-success"
                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                        data-btn-cancel-class="btn-danger"
                        data-title="Continue action?" data-content="This will trash the item">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
                </button>
            </div>
        </td>
    </tr>
@endforeach