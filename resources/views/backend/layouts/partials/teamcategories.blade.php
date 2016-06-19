<div class="row">
    <div class="col-xs-12">
        <h2 class="page-header pull-left"><a href="#edit" class="edit-in-place editable editable-pre-wrapped editable-click" data-name="name" id="name" data-type="text" data-pk="{{ $category->id }}" data-url="{{ route('admin.teams.categories.update', [$category->id]) }}" data-title="Edit category">{{ $category->label }}</a></h2>
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
                        <li>{{ link_to_route('admin.teams.edit', 'Edit Category', [$category->id, 0]) }}</li>
                        <li>{{ link_to_route('admin.teams.categories.delete', 'Delete Category', [$category->id], ['data-token' => csrf_token(), 'data-method' => 'delete', 'rel' => 'nofollow', 'data-confirm' => 'true']) }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        @foreach($category->teams as $team)
            @include('backend.layouts.partials.teammembers')
        @endforeach
    </div>
</div>
<hr>