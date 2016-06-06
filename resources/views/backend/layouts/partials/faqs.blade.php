<tr>
    <td>{{ $faq->question }}</td>
    <td>{{ $faq->answer }}</td>
    <td>
        <div class="btn-toolbar">
            <a href="{{ route('admin.faqs.edit', ['category' => $category->id, 'faq' => $faq->id]) }}"
               title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs"
               role="button"><span
                        class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') -->
            </a>
            <a href="{{ route('admin.faqs.delete', ['category' => $category->id, 'faq' => $faq->id]) }}"
               title="@lang('buttons.deleteTitle')"
               class="btn btn-danger action_confirm btn-xs" role="button"
               data-token="{{ Session::getToken() }}" data-method="delete"><span
                        class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
            </a>
        </div>
    </td>
</tr>