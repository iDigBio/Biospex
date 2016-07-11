<div class="box box-info collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title" data-widget="collapse">{{ $category->name }} <i class="fa fa-plus btn-box-tool"></i></h3>
        <div class="box-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <div class="btn-toolbar">
                    <button title="@lang('buttons.create')" class="btn btn-primary btn-sm" type="button"
                            onClick="location.href='{{ route('admin.faqs.create', [$category->id]) }}'">
                        <span class="fa fa-plus fa-sm"></span></button>

                    <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm" type="button"
                            onClick="location.href='{{ route('admin.faqs.categories.edit', [$category->id, 0]) }}'"><span
                                class="fa fa-cog fa-sm"></span></button>

                    <button title="@lang('buttons.deleteTitle')" class="btn btn-danger btn-sm" type="button"
                            data-toggle="confirmation" data-placement="left"
                            data-href="{{ route('admin.faqs.delete', [$category->id, 0]) }}"
                            data-method="delete">
                        <span class="fa fa-remove fa-sm"></span></button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table class="table table-hover">
            <tr>
                <th>Question</th>
                <th>Answer</th>
                <th></th>
            </tr>
            @foreach($category->faqs as $faq)
                @include('backend.faqs.partials.faqs')
            @endforeach
        </table>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->