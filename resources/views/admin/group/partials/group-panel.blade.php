<div class="row">
    <div class="col-sm-10 mx-auto">
        <div class="jumbotron box-shadow pt-2 pb-5 my-5 p-sm-5">
            <h1 class="text-center content-header text-uppercase">{{ $group->title }}</h1>
            <div class="col-12">
                <div class="d-flex justify-content-between mt-4 mb-4">
                    {!! $group->present()->group_invite_icon_lrg !!}
                    {!! $group->present()->group_edit_icon_lrg !!}
                    @can('isOwner', $group)
                        {!! $group->present()->group_delete_icon_lrg !!}
                    @endcan
                </div>
                <hr class="header mx-auto" style="width:300px;">
                <div class="d-flex justify-content-between mt-4">
                    <span class="text">{{ $group->users->count() }} {{ t('Members') }} </span>
                    <span class="text">{{ $group->projects->count() }} {{ t('Projects') }} </span>
                    <span class="text">{{ $group->expeditions_count }} {{ t('Expeditions') }} </span>
                </div>
            </div>
        </div>
    </div>
</div>
