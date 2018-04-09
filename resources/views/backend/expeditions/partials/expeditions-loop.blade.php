@foreach($expeditions as $expedition)
    <tr id="{{ $expedition->id }}">
        <td>{!! $expedition->id !!}</td>
        <td>{!! $expedition->title !!}</td>
        <td>
        <td>
            <div class="btn-toolbar">
                <button title="@lang('pages.editTitle')" class="btn btn-warning btn-xs" type="button"
                        onClick="location.href='{{ route('admin.expeditions.edit', [$expedition->id]) }}'">
                    <span class="fa fa-wrench fa-sm"></span> <!-- @lang('pages.edit') --></button>

                <button class="btn btn-xs btn-danger" title="@lang('pages.deleteTitle')"
                        data-href="{{ route('admin.expeditions.delete', [$expedition->id]) }}"
                        data-method="delete"
                        data-toggle="confirmation"
                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                        data-btn-ok-class="btn-success"
                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                        data-btn-cancel-class="btn-danger"
                        data-title="Continue action?" data-content="This will trash the item">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('pages.delete') -->
                </button>
            </div>
        </td>
    </tr>
@endforeach