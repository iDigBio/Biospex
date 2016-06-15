<div class="row">
    <div class="col-xs-12">
        <h2 class="page-header pull-left">{{ $category->label }}</h2>
        <div class="box-tools ">
            <div class="input-group">
                <div class="btn-group action-fix">
                    <button type="button" class="btn btn-sm btn-primary">Actions</button>
                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="{{ route('admin.teams.create', ['category' => $category->id]) }}">Add Member</a>
                        </li>
                        <li><a href="{{ route('admin.teams.edit', ['category' => $category->id, 'team' => 0]) }}">Edit Category</a>
                        </li>
                        <li><a href="{{ route('admin.teams.delete', ['category' => $category->id, 'team' => 0]) }}"
                               class="action_confirm" data-token="{{ Session::getToken() }}"
                               data-method="delete">Delete Category</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        @foreach($category->teams as $member)
            @include('backend.layouts.partials.teammembers')
        @endforeach
    </div>
</div>
<hr>