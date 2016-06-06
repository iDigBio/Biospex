<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ $category->label }}</h3>
        <div class="box-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm">Actions</button>
                    <button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="{{ route('admin.faqs.create', ['category' => $category->id]) }}">Add Question</a>
                        </li>
                        <li><a href="{{ route('admin.faqs.edit', ['category' => $category->id, 'faq' => 0]) }}">Edit Category</a>
                        </li>
                        <li><a href="{{ route('admin.faqs.delete', ['category' => $category->id, 'faq' => 0]) }}"
                               class="action_confirm" data-token="{{ Session::getToken() }}"
                               data-method="delete">Delete Category</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <tr>
                <th>Question</th>
                <th>Answer</th>
                <th></th>
            </tr>
            @foreach($category->faqs as $faq)
                @include('backend.layouts.partials.faqs')
            @endforeach
        </table>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->