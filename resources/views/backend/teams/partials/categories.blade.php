<div class="row">
    <div class="col-xs-12">
        <h2 class="page-header pull-left">{{ $category->name }}</h2>
        <div class="box-tools ">
            <div class="input-group input-group-sm action-fix" style="width: 150px;">
                <div class="btn-toolbar">
                    <button title="@lang('buttons.create')" class="btn btn-primary btn-sm" type="button"
                            onClick="location.href='{{ route('admin.teams.create', [$category->id]) }}'">
                        <span class="fa fa-plus fa-sm"></span></button>

                    <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm" type="button"
                            onClick="location.href='{{ route('admin.teams.categories.edit', [$category->id, 0]) }}'"><span
                                class="fa fa-cog fa-sm"></span></button>

                    <button title="@lang('buttons.deleteTitle')" class="btn btn-danger btn-sm" type="button"
                            data-toggle="confirmation" data-placement="left"
                            data-href="{{ route('admin.teams.delete', [$category->id, 0]) }}"
                            data-method="delete">
                        <span class="fa fa-remove fa-sm"></span></button>
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