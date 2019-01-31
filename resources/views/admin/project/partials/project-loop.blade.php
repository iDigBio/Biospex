<div class="col-md-4 mb-4">
    <div class="card px-4 box-shadow h-100">
        <h2 class="text-center pt-4">{{ $project->title }}</h2>
        <hr>
        <div class="row card-body">
            <div class="col-7">
                <ul class="text">
                    <li>{{ $project->group->title }}</li>
                    <li class="mt-3">{{ $project->expeditions_count }} {{ __('Expeditions') }}</li>
                    <li>{{ CountHelper::projectTranscriberCount($project->id) }} {{ __('Transcribers') }}</li>
                    <li>{{ CountHelper::projectTranscriptionCount($project->id) }} {{ __('Transcriptions') }}</li>
                </ul>
            </div>

            <div class="col-5">
                <img class="img-fluid" src="{{ $project->present()->logo_url }}" alt="Card image cap">
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                {!! $project->present()->project_page_icon !!}
                {!! $project->present()->project_show_icon !!}
                {!! $project->present()->project_import_icon !!}
                {!! $project->present()->project_explore_icon !!}
                {!! $project->present()->project_advertise_icon !!}
                {!! $project->present()->project_statistics_icon !!}
                {!! $project->present()->project_ocr_icon !!}
                {!! $project->present()->project_edit_icon !!}
                {!! $project->present()->project_clone_icon !!}
                @can('isOwner', $project->group)
                    {!! $project->present()->project_delete_icon !!}
                @endcan
            </div>
        </div>
    </div>
</div>