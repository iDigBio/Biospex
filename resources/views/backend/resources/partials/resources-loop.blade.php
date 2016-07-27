@foreach($resources as $resource)
    <tr id="{{ $resource->id }}">
        <td class="order">{{ $resource->order }}</td>
        <td>{!! $resource->title !!}</td>
        <td>{!! $resource->description !!}</td>
        <td>{{ link_to_route('web.resources.download', $resource->document, [$resource->id]) }}</td>
        <td><td class="button-fix">
            <div class="btn-toolbar">
                <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                        onClick="location.href='{{ route('admin.resources.edit', [$resource->id]) }}'">
                    <span class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') --></button>

                <button title="@lang('buttons.deleteTitle')" class="btn btn-danger btn-xs" type="button"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('admin.resources.delete', [$resource->id]) }}"
                        data-method="delete">
                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
            </div>
        </td>
    </tr>
@endforeach