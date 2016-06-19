<tr>
    <td>{{ $faq->question }}</td>
    <td>{!! $faq->answer !!}</td>
    <td class="button-fix">
        <div class="btn-toolbar">
            <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                    onClick="location.href='{{ route('admin.faqs.edit', [$category->id, $faq->id]) }}'">
                <span class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') --></button>

            <button title="@lang('buttons.deleteTitle')" class="btn btn-danger delete-form btn-xs"
                    data-href="{{ route('admin.faqs.delete', [$category->id, $faq->id]) }}"
                    data-confirm="Are you sure you wish to delete?" data-method="delete">
                <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
        </div>
    </td>
</tr>