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
                        <li>{{ link_to_route('admin.teams.create', 'Add Member', [$category->id]) }}</li>
                        <li>{{ link_to_route('admin.teams.categories.edit', 'Edit Category', [$category->id, 0]) }}</li>
                        <li>{{ link_to_route('admin.teams.delete', 'Delete Category', [$category->id, 0], ['data-method' => 'delete', 'rel' => 'nofollow', 'data-confirm' => 'Are you sure you want to delete this category?']) }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        @foreach($category->teams as $team)
            @include('backend.teams.partials.teams')
        @endforeach
    </div>
</div>
<hr>