<div class="box box-info collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title" data-widget="collapse">{{ $category->name }} <i class="fa fa-plus btn-box-tool"></i></h3>
        <div class="box-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm">Actions</button>
                    <button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li>{{ link_to_route('admin.faqs.create', 'Add Question', [$category->id]) }}</li>
                        <li>{{ link_to_route('admin.faqs.categories.edit', 'Edit Category', [$category->id, 0]) }}</li>
                        <li>{{ link_to_route('admin.faqs.delete', 'Delete Category', [$category->id, 0], ['data-method' => 'delete', 'data-confirm' => 'Are you sure you wish to delete?', 'rel' => 'nofollow']) }}</li>
                    </ul>
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