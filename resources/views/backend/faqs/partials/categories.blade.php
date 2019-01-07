<div class="box box-info collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title" data-widget="collapse">{{ $category->name }} <i class="fa fa-plus btn-box-tool"></i></h3>
        <div class="box-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <div class="btn-toolbar">
                    <button title="@lang('pages.create')" class="btn btn-primary btn-sm" type="button"
                            onClick="location.href='{{ route('admin.faqs.create', [$category->id]) }}'">
                        <span class="fa fa-plus fa-sm"></span></button>

                    <button title="@lang('pages.editTitle')" class="btn btn-warning btn-sm" type="button"
                            onClick="location.href='{{ route('admin.faqs.categories.edit', [$category->id, 0]) }}'"><span
                                class="fa fa-cog fa-sm"></span></button>

                    <button class="btn btn-sm btn-danger" title="@lang('pages.deleteTitle')"
                            data-href="{{ route('admin.faqs.delete', [$category->id, 0]) }}"
                            data-method="delete"
                            data-toggle="confirmation"
                            data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                            data-btn-ok-class="btn-success"
                            data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                            data-btn-cancel-class="btn-danger"
                            data-title="Continue action?" data-content="This will delete the item">
                        <span class="fa fa-remove fa-sm"></span>
                    </button>
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