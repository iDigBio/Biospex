@foreach($actors as $actor)
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

                <button title="@lang('buttons.deleteTitle')" class="btn btn-danger btn-xs" type="button"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('admin.actors.delete', [$actor->id]) }}"
                        data-method="delete">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
            </div>
        </td>
    </tr>
@endforeach