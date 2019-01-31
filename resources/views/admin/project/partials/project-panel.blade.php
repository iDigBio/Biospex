<div class="row">
    <div class="col-sm-10 mx-auto">
        <div class="jumbotron box-shadow pt-2 pb-5 my-5 p-sm-5">
            <h1 class="text-center project-wide text-uppercase">{{ $project->title }}</h1>
            <div class="col-12">
                <div class="d-flex justify-content-between mt-4 mb-3">
                    {!! $project->present()->project_page_icon_lrg !!}
                    {!! $project->present()->project_show_icon_lrg !!}
                    {!! $project->present()->project_import_icon_lrg !!}
                    {!! $project->present()->project_explore_icon_lrg !!}
                    {!! $project->present()->project_advertise_icon_lrg !!}
                    {!! $project->present()->project_statistics_icon_lrg !!}
                    {!! $project->present()->project_ocr_icon_lrg !!}
                    {!! $project->present()->project_edit_icon_lrg !!}
                    {!! $project->present()->project_clone_icon_lrg !!}
                    @can('isOwner', $project->group)
                        {!! $project->present()->project_delete_icon_lrg !!}
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.partials.import-modal')