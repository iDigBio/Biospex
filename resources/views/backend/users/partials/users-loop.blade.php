@foreach($users as $user)
    <tr id="{{ $user->id }}">
        <td>{!! $user->id !!}</td>
        <td>{!! $user->profile->full_name !!}</td>
        <td>{!! $user->email !!}</td>
        <td>{!! $user->activated === 1 ? 'Yes' : 'No' !!}</td>
        <td>{!! $user->profile->timezone !!}</td>
        <td>{!! $user->created_at !!}</td>
        <td>
        <td>
            <div class="btn-toolbar">
                <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                        onClick="location.href='{{ route('admin.users.edit', [$user->id]) }}'">
                    <span class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') --></button>

                <button class="btn btn-xs btn-danger" title="@lang('buttons.deleteTitle')"
                        data-href="{{ route('admin.users.delete', [$user->id]) }}"
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