@foreach($pages as $page)
    <tr>
        <td>{{ $page->id }}</td>
        <td>{{ $page->key }}</td>
        <td>{!! $page->value !!}</td>
        <td><td class="button-fix">
            <div class="btn-toolbar">
                <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                        onClick="location.href='{{ route('admin.pages.edit', [$page->id]) }}'">
                    <span class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') --></button>
            </div>
        </td>
    </tr>
@endforeach